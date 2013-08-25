<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\UserBundle\Model\InternalQuery;

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
    protected $logger;
    protected $debug;

    /**
     * construct
     * @param NotifierInterface $notifier
     * @param LoggerInterface   $logger
     * @param bool              $debug
     */
    public function __construct(NotifierInterface $notifier, LoggerInterface $logger, $debug)
    {
        $this->notifier = $notifier;
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

            }

            // saving with NestedSet
            if ($internal->isNew()) {
                $internal->insertAsLastChildOf($parent, $pdo);
            }
            else {
                $internal->moveToLastChildOf($parent, $pdo);
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