<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\NotificationBundle\Notification\NotifierInterface;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * base form handler for admin forms
 *
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
abstract class AdminHandler
{
    protected $notifier;
    protected $securityContext;
    protected $validator;
    protected $rootDir;
    protected $logger;
    protected $debug = false;

    /**
     * set notifier
     * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
     */
    public function setNotifier(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * set security context
     * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
     */
    public function setSecurityContext(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * set logger and debug level if defined
     * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
     */
    public function setLogger(LoggerInterface $logger, $debug = false)
    {
        $this->logger = $logger;
        $this->debug  = $debug;
    }

    /**
     * set validator service
     * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * set projet root dir
     * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = realpath($rootDir.'/..');
    }

    /**
     * injects given error repport into given form
     *
     * @param  Form                    $form
     * @param  ConstraintViolationList $errorReport
     * @return int
     */
    public function injectsErrors(Form $form, ConstraintViolationList$errorReport)
    {
        if (!$errorReport->count()) {
            return 0;
        }

        foreach ($errorReport as $violation) {
            $form->addError(new FormError(
                $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getMessageParameters(),
                $violation->getMessagePluralization()
            ));
        }

        return $errorReport->count();
    }

}
