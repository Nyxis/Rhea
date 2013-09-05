<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\UserBundle\Model\Internal;

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
     * handle password updating for an internal
     *
     * @param Form     $form
     * @param Internal $internal
     */
    public function handleInternalPassword(Form $form, Internal $internal)
    {
        if (!$form->has('update_password')
            || !$form->get('update_password')->getData()) {
            return;
        }

        $internal->setPassword(sha1($form->get('password')->getData()));
    }

    /**
     * handle an image form as an internal image
     *
     * @param  Form     $form
     * @param  Internal $internal
     * @return string
     */
    public function handleInternalImage(Form $form, Internal $internal)
    {
        if (!$form->has('image') || !($image = $form->get('image')->getData())) {
            return;
        }

        try {
            $extension = $image->guessExtension();

            if (!in_array($extension, array('jpeg', 'png'))) {
                $this->notifier->add('warning', 'internal.admin.notifications.invalid_image');
            } else {
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
                throw $e;
            }

            $this->logger->err($e->getMessage());
            $this->notifier->add('error', 'internal.admin.notifications.error_image');
        }
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
