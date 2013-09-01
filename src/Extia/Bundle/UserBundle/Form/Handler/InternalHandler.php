<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\Resignation;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;

use Extia\Bundle\TaskBundle\Model\TaskQuery;
use Extia\Bundle\MissionBundle\Model\MissionQuery;
use Extia\Bundle\NotificationBundle\Notification\NotifierInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Form handler for internals creation / editing
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class InternalHandler
{
    protected $securityContext;
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
    public function __construct(SecurityContextInterface $securityContext, NotifierInterface $notifier, $rootDir, LoggerInterface $logger, $debug)
    {
        $this->securityContext = $securityContext;
        $this->notifier        = $notifier;
        $this->rootDir         = $rootDir;
        $this->logger          = $logger;
        $this->debug           = $debug;
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
            $this->notifier->add('warning', 'internal.admin.notifications.invalid_form');

            return false;
        }

        $internal = $form->getData();
        $image    = $form->get('image')->getData();

        try {
            if (!empty($image)) {   // image uploading

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
                        $pdo->rollback();
                        throw $e;
                    }

                    $this->logger->err($e->getMessage());
                    $this->notifier->add('error', 'internal.admin.notifications.error_image');
                }
            }

            // saving with NestedSet
            if ($internal->isNew()) {
                $internal->insertAsLastChildOf($parent, $pdo);
            } else {
                $internal->moveToLastChildOf($parent, $pdo);
            }

            if (!$internal->isNew()) {
                $resignation = $form->get('resignation')->getData();
                if (!empty($resignation['resign_internal'])) {

                    // creates a new resignation
                    $resign = new Resignation();
                    $resign->setLeaveAt($resignation['leave_at']);
                    $resign->setCode($resignation['resignation_code']);
                    $resign->setComment($resignation['reason']);
                    $resign->setResignedById($this->securityContext->getToken()->getUser()->getId());

                    $internal->setResignation($resign);

                    // re-assign all
                    $internalId = $resignation['assign_all_to'];

                    // tasks
                    $nbTasks = TaskQuery::create()
                        ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                        ->filterByAssignedTo($internal->getId())
                        ->update(array('AssignedTo' => $internalId), $pdo);

                    // missions
                    $nbMissions = MissionQuery::create()
                        ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                        ->filterByManagerId($internal->getId())
                        ->update(array('ManagerId' => $internalId), $pdo);

                    // consultants as crh
                    $nbCltCrh = ConsultantQuery::create()
                        ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                        ->filterByCrhId($internal->getId())
                        ->update(array('CrhId' => $internalId), $pdo);

                    // consultants as mng
                    $nbCltMng = ConsultantQuery::create()
                        ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                        ->filterByManagerId($internal->getId())
                        ->update(array('ManagerId' => $internalId), $pdo);

                    // assign user team to parent
                    foreach ($internal->getChildren($pdo) as $child) {
                        $child->moveToLastChildOf($internal->getParent($pdo));
                        $child->save($pdo);
                    }
                }
            }

            $internal->save($pdo);

            // reinjects credentials into person (cascade save doesnt work with inheritance)
            $internal->getPerson()->setPersonCredentials(
                $internal->getPersonCredentials()
            );

            $internal->save($pdo);

            $pdo->commit();

            // success message
            $this->notifier->add(
                'success', 'internal.admin.notifications.save_success',
                array ('%internal_name%' => $internal->getLongName())
            );

            return true;

        } catch (\Exception $e) {
            $pdo->rollback();

            if ($this->debug) {
                throw $e;
            }

            $this->logger->err($e->getMessage());
            $this->notifier->add(
                'error', 'internal.admin.notifications.error'
            );

            return false;
        }
    }
}
