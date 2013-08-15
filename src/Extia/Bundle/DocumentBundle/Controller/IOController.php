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
            return JsonResponse::create(array(
                'error' => 'invalid_form'
            ));
        }

        $data = $form->getData();
        $file = $data['file'];

        if (!$this->get('document.factory')->supports($file)) {
            return JsonResponse::create(array('error' => 415), 415);
        }

        $document->replaceFile($file)->save();

        return JsonResponse::create(array(
            'success'  => true,
            'ext'      => $document->getType(),
            'filename' => $document->getSimpleName()
        ));
    }
}
