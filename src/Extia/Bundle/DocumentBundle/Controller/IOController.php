<?php

namespace Extia\Bundle\DocumentBundle\Controller;

use Extia\Bundle\DocumentBundle\Model\Document;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * controller for upload and download documents
 */
class IOController extends Controller
{
    /**
     * downloads requested Document
     * @param  Request  $request
     * @param  Document $document
     * @return Response
     */
    public function downloadAction(Request $request, Document $document)
    {
        $documentPath = $document->getPath();

        return StreamedResponse::create(
            function () use ($documentPath) { readfile($documentPath); },
            200,  // http status
            array(
                'Content-Description'       => 'File Transfer',
                'Content-Type'              => 'application/octet-stream',
                'Content-Disposition'       => 'attachment; filename='.basename($documentPath),
                'Content-Transfer-Encoding' => 'binary',
                'Expires'                   => '0',
                'Cache-Control'             => 'must-revalidate',
                'Pragma'                    => 'public',
                'Content-Length'            => filesize($documentPath),
            )
        );
    }

    /**
     * uploads given file for given Document
     * @param  Request  $request
     * @param  Document $document
     * @return Response
     */
    public function uploadAction(Request $request, Document $document)
    {
        $form = $this->get('extia_document.form.upload');

        if (!$request->request->has($form->getName())) {
            throw new NotFoundHttpException(sprintf('Any proper file has been given, abording.'));
        }

        $form->bind($request);
        if (!$form->isValid()) {
            $errorNotice = $this->get('notifier')->add('warning', 'document.upload.notification.invalid_form')->get('warning');
            $errorNotice = array_pop($errorNotice);

            return JsonResponse::create(array(
                'error'   => true,
                'message' => $errorNotice
            ));
        }

        try {
            $data = $form->getData();
            $file = $data['file'];

            $path   = $document->getPath();
            $newExt = $file->guessExtension();
            $oldExt = $document->getType();

            if ($newExt != $oldExt) {
                $filename = str_replace('.'.$oldExt, '.'.$newExt, $document->getName());
                $document->setName($filename);

                $document->setType($newExt);

                $path = dirname($path).'/'.$filename;
                $document->setPath($path);
            }

            $file->move(dirname($path), basename($path));

            $document->save();

        } catch (\Exception $e) {
            $errorNotice = $this->get('notifier')->add('error',
                    $this->container->getParameter('kernel.debug') ?
                        $e->getMessage() :
                        'document.upload.notification.error'
                )->get('error');

            return JsonResponse::create(array(
                'error'   => true,
                'message' => array_pop($errorNotice)
            ));
        }

        $successNotif = $this->get('notifier')->add('success', 'document.upload.notification.success')->get('success');
        $successNotif = array_pop($successNotif);

        return JsonResponse::create(array(
            'success'  => true,
            'ext'      => $document->getType(),
            'filename' => $document->getSimpleName(),
            'message'  => $successNotif
        ));
    }
}
