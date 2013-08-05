<?php

namespace Extia\Bundle\GroupBundle\Form\Handler;

use Extia\Bundle\GroupBundle\Model\Group;

use Extia\Bundle\NotificationBundle\Notification\NotifierInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * handler for Group forms
 */
class GroupHandler
{
    protected $notifier;
    protected $logger;
    protected $debug;

    /**
     * construct
     * @param NotifierInterface $notifier
     * @param LoggerInterface   $logger
     * @param bool              $debug    app debug state
     */
    public function __construct(NotifierInterface $notifier, LoggerInterface $logger, $debug)
    {
        $this->notifier = $notifier;
        $this->logger   = $logger;
        $this->debug    = $debug;
    }

    /**
     * handle method, process form and save model
     * @param  Form    $form
     * @param  Request $request
     * @return bool
     */
    public function handle(Form $form, Request $request)
    {
        $form->bind($request);

        if (!$form->isValid()) {
            $this->notifier->add('warning', 'group.admin.notification.validation_warning');

            return false;
        }

        $con = \Propel::getConnection('default');
        $con->beginTransaction();

        try {
            $group = $form->getData();
            $group->save();

            $con->commit();
        } catch (\Exception $e) {
            $con->rollback();

            $this->error = 'group.admin.notification.save_error';
            $this->logger->err($e->getMessage());

            if ($this->debug) {
                throw $e;
            }

            return false;
        }

        return true;
    }
}
