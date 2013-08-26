<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\Resignation;

use Extia\Bundle\NotificationBundle\Notification\NotifierInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * Form handler for internals creation / editing
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class InternalHandler
{
    protected $notifier;
    protected $rootDir;
    protected $logger;
    protected $debug;

    /**
     * construct
     * @param NotifierInterface $notifier
     * @param string            $rootDir
     * @param LoggerInterface   $logger
     * @param bool              $debug
     */
    public function __construct(NotifierInterface $notifier, $rootDir, LoggerInterface $logger, $debug)
    {
        $this->notifier = $notifier;
        $this->rootDir  = $rootDir;
        $this->logger   = $logger;
        $this->debug    = $debug;
    }

    /**
     * handle method
     * @param  Request $request
     * @param  Form    $form
     * @return bool
     */
    public function handle(Form $form, Request $request)
    {
        $form->submit($request);

        $pdo = \Propel::getConnection('default');
        $pdo->beginTransaction();

        $parentId = $form->get('parent')->getData();
        $parent   = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->findPk($parentId, $pdo);

        if (!$form->isValid() || empty($parent)) {
            $this->notifier->add('warning', 'internal.admin.notification.invalid_form');

            return false;
        }

        $internal = $form->getData();
        $image    = $form->get('image')->getData();

        try {
            if (!empty($image)) {   // image uploading

                try {
                    $extension = $image->guessExtension();
                    if (!in_array($extension, array('jpeg', 'png'))) {
                        $this->notifier->add('warning', 'internal.admin.notification.invalid_image');
                    }
                    else {
                        $fileName = $internal->getUrl().'.'.$extension;
                        $webPath  = 'images/avatars/';
                        $path     = sprintf('%s/../web/%s', $this->rootDir, $webPath);

                        if (!is_dir($path)) {
                            mkdir($path); // will throw an error if access denied caught below
                        }

                        $physicalDoc = $image->move($path, $fileName);
                        $internal->setImage($webPath.$fileName);
                    }
                } catch (\Exception $e) {
                    if ($this->debug) {
                        $pdo->rollback();
                        throw $e;
                    }

                    $this->logger->err($e->getMessage());
                    $this->notifier->add('error', 'internal.admin.notification.error_image');
                }
            }

            // saving with NestedSet
            if ($internal->isNew()) {
                $internal->insertAsLastChildOf($parent, $pdo);
            }
            else {
                $internal->moveToLastChildOf($parent, $pdo);
            }

            // reinjects credentials into person (cascade save doesnt work with inheritance)
            $internal->getPerson()->setPersonCredentials(
                $internal->getPersonCredentials()
            );

            if (!$internal->isNew()) {
                $resignation = $form->get('resignation')->getData();
                if (empty($resignation['resign_internal'])) {
                    $this->notifier->add('warning', 'internal.admin.notification.invalid_form');
                    return false;
                }
                else {
                    // creates a new resignation
                    $resign = new Resignation();
                    $resign->setLeaveAt($resignation['leave_at']);
                    $resign->setCode($resignation['resignation_code']);
                    $resign->setComment($resignation['reason']);

                    $internal->setResignation($resign);

                    // re-assign all

                    // @todo
                }
            }

            $internal->save($pdo);

            $pdo->commit();

            // success message
            $this->notifier->add(
                'success', 'internal.admin.notification.save_success',
                array ('%internal_name%' => $internal->getLongName())
            );

            return true;

        } catch(\Exception $e) {
            $pdo->rollback();

            if ($this->debug) {
                throw $e;
            }

            $this->logger->err($e->getMessage());
            $this->notifier->add(
                'error', 'internal.admin.notification.error'
            );

            return false;
        }
    }
}