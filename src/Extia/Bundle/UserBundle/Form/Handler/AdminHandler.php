<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\NotificationBundle\Notification\NotifierInterface;
use Extia\Bundle\MissionBundle\Model\MissionQuery;
use Extia\Bundle\MissionBundle\Model\ClientQuery;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * base form handler for admin forms
 *
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
abstract class AdminHandler
{
    protected $notifier;
    protected $session;
    protected $securityContext;
    protected $validator;
    protected $translator;
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
    public function setSession(SessionInterface $session, SecurityContextInterface $securityContext)
    {
        $this->session         = $session;
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
     * set translator
     * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
     * @param  ConstraintViolationList $errorReport = null
     * @return int
     */
    public function injectsErrors(Form $form, ConstraintViolationList $errorReport = null)
    {
        if ($this->session->getFlashbag()->has('form_violations_'.$form->getName())) {
            $count = 0;

            foreach ($this->session->getFlashbag()->get('form_violations_'.$form->getName()) as $violations) {
                foreach ($violations as $formName => $errorReport) {
                    if (!$form->has($formName)) {
                        continue;
                    }

                    $count += $this->injectsErrors($form->get($formName), $errorReport);
                }
            }

            return $count;
        }

        if (empty($errorReport) || !$errorReport->count()) {
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

    /**
     * stores given errors to session for next use
     *
     * @param  Form                    $form
     * @param  ConstraintViolationList $errorReport
     * @return int
     */
    public function storeErrors(Form $form, ConstraintViolationList $errorReport)
    {
        $this->session->getFlashbag()->add('form_violations_'.$form->getParent()->getName(),
            array($form->getName() => $errorReport)
        );

        return $errorReport->count();
    }

    /**
     * return manager special mission (ic, waiting ...)
     *
     * @param  int     $managerId
     * @param  string  $missionType
     * @return Mission
     */
    protected function getManagerMission($managerId, $missionType, \Pdo $pdo = null)
    {
        $mission = MissionQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByType($missionType)
            ->filterByManagerId($managerId)
            ->filterByClientId(
                ClientQuery::create()
                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                    ->select('Id')
                    ->findOneByTitle('Extia')
            )
            ->findOneOrCreate($pdo)
        ;

        if ($mission->isNew()) {
            $mission->setLabel('Intercontrat');
            $mission->save($pdo);
        }

        return $mission;
    }

}
