<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\PersonTypeQuery;
use Extia\Bundle\UserBundle\Model\MissionOrder;
use Extia\Bundle\UserBundle\Model\Resignation;

use Extia\Bundle\MissionBundle\Model\MissionQuery;
use Extia\Bundle\MissionBundle\Model\ClientQuery;

use Extia\Bundle\TaskBundle\Workflow\Aggregator;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;
use EasyTask\Bundle\WorkflowBundle\Model\WorkflowNodeQuery;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form handler for consultant creation / editing
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class ConsultantHandler extends AdminHandler
{
    protected $workflows;

    /**
     * construct
     * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
     */
    public function __construct(Aggregator $workflows)
    {
        $this->workflows = $workflows;
    }

    /**
     * tests if form is valid
     *
     * @param  Form $form
     * @return bool
     */
    public function isValid(Form $form)
    {
        $return = true;

        if (!$form->isValid()) {
            $return = false;
        }

        // mission use case :
        //    - have to has on_profile true + manager
        //    - have to has on_profile false + mission

        if ($form->has('on_profile')) {
            if ($form->get('on_profile')->getData()) {
                // valid manager is not empty
                $errorReport = $this->validator->validateValue(
                    $form->get('manager_id')->getData(), new Assert\NotBlank()
                );

                $return = $this->injectsErrors($form->get('manager_id'), $errorReport) < 1 && $return;
            } else {
                // valid mission is not empty
                $errorReport = $this->validator->validateValue(
                    $form->get('mission')->getData(), new Assert\NotBlank()
                );

                $return = $this->injectsErrors($form->get('mission'), $errorReport) < 1 && $return;
            }
        }

        $this->catchEmailExistsError($form);

        return $return;
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

        if (!$this->isValid($form)) {
            $this->notifier->add('warning', 'consultant.admin.notifications.invalid_form');

            return false;
        }

        $pdo = \Propel::getConnection('default');
        $pdo->beginTransaction();

        $consultant = $form->getData();
        $image      = $form->get('image')->getData();

        try {
            // image
            $this->handleInternalImage($form, $consultant);

            $crh = InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->findPk($consultant->getCrhId(), $pdo)
            ;

            $consultant->setPersonTypeId(
                PersonTypeQuery::create()
                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                    ->select('Id')
                    ->findOneByCode('clt')
            );

            // mission
            if ($consultant->isNew() && $form->has('on_profile')) { // replace with form type "mission"

                $missionOrder = new MissionOrder();
                $missionOrder->setConsultant($consultant);
                $missionOrder->setBeginDate($form->get('begin_at')->getData());
                $missionOrder->setCurrent(true);

                $onProfile = $form->get('on_profile')->getData();
                if ($onProfile) {

                    $managerId = $form->get('manager_id')->getData();
                    if (empty($managerId)) {

                    } else {
                        // look for manager "on profile mission"
                        $mission = MissionQuery::create()
                            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                            ->filterByType('waiting')
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
                            $mission->setLabel('Recrutement sur profil');
                        }
                    }
                } else {
                    $missionId = $form->get('mission')->getData();

                    // retrieve mission
                    $mission = MissionQuery::create()
                        ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                        ->findPk($missionId, $pdo);

                    // mission starts more than 2 days after contract conclusion -> create a waiting mission order
                    if (($missionOrder->getBeginDate('U') - $consultant->getContractBeginDate('U')) > 48*3600) {

                        $waitingMission = MissionQuery::create()
                            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                            ->filterByType('waiting')
                            ->filterByManagerId($mission->getManagerId())
                            ->filterByClientId(
                                ClientQuery::create()
                                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                                    ->select('Id')
                                    ->findOneByTitle('Extia')
                            )
                            ->findOneOrCreate($pdo)
                        ;

                        if ($waitingMission->isNew()) {
                            $waitingMission->setLabel('Recrutement sur profil');
                        }

                        $waitingMissionOrder = new MissionOrder();
                        $waitingMissionOrder->setMission($waitingMission);
                        $waitingMissionOrder->setConsultant($consultant);
                        $waitingMissionOrder->setBeginDate($consultant->getContractBeginDate());
                        $waitingMissionOrder->setEndDate($missionOrder->getBeginDate());
                    }
                }

                $missionOrder->setMission($mission);
                $consultant->setManagerId($mission->getManagerId());
            }

            // saving with NestedSet
            if ($consultant->isNew()) {
                $consultant->insertAsLastChildOf($crh, $pdo);
            } elseif ($consultant->isColumnModified('CrhId')) {
                $consultant->moveToLastChildOf($crh, $pdo);
            }

            $consultant->save($pdo);

            // reinjects credentials into person (cascade save doesnt work with inheritance)
            $consultant->getPerson()->setPersonCredentials(
                $consultant->getPersonCredentials()
            );

            // success message
            $this->notifier->add(
                'success', 'consultant.admin.notifications.save_success',
                array ('%consultant_name%' => $consultant->getLongName())
            );

            // resignation
            if ($form->has('resignation')) {
                $resignation = $form->get('resignation')->getData();
                if (!empty($resignation['resign_consultant'])) {

                    // creates a new resignation
                    $resign = new Resignation();
                    $resign->setLeaveAt($resignation['leave_at']);
                    $resign->setCode($resignation['resignation_code']);
                    $resign->setComment($resignation['reason']);
                    $resign->setResignedById($this->securityContext->getToken()->getUser()->getId());

                    $consultant->setResignation($resign);

                    // close tasks
                    if (in_array('close_tasks', $resignation['options'])) {
                        $nodesToDesativate = WorkflowNodeQuery::create()
                            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                            ->filterByCurrent(true)
                            ->useTaskQuery()
                                ->filterByUserTargetId($consultant->getId())
                            ->endUse()
                            ->find($pdo);

                        $nbTasks = $nodesToDesativate->count();
                        foreach ($nodesToDesativate as $node) {
                            $node->setCurrent(false);
                            $node->setEnded(true);
                            $node->save($pdo);
                        }
                    }

                    // end mission
                    if (in_array('end_mission', $resignation['options'])) {
                        $lastMissionOrder = $consultant->getCurrentMissionOrder($pdo);
                        $lastMissionOrder->setCurrent(false);
                        $lastMissionOrder->setEndDate($resign->getLeaveAt());
                        $lastMissionOrder->save($pdo);
                    }

                    $consultant->save($pdo);
                }
            }

            // task creation
            if ($form->has('create_crh_monitoring') && true == $form->get('create_crh_monitoring')->getData()) {
                $wfCrh = $this->workflows->create('crh_monitoring');

                $this->workflows->boot($wfCrh, $request, $pdo);  // first step

                // second step : resolve like an user posted a form
                $task = $this->workflows->getCurrentTask($wfCrh, $pdo);
                $task->getNode()->getType()->getHandler()->resolve(array(
                        'user_target_id' => $consultant->getId(),
                        'next_date'      => $task->findNextWorkingDay(
                            (int) $task->calculateDate($consultant->getContractBeginDate(), '+1 month', 'U')
                        ),
                        'workflow' => array(
                            'name'           => sprintf('%s %s', $this->translator->trans('crh_monitoring'), $consultant->getLongName()),
                            'description'    => sprintf('%s %s', $this->translator->trans('crh_monitoring'), $consultant->getLongName())
                        )
                    ), $task, $request, $pdo
                );
            }

            if ($form->has('create_annual_review') && true == $form->get('create_annual_review')->getData()) {
                $wfAnnualReview = $this->workflows->create('annual_review');

                $this->workflows->boot($wfAnnualReview, $request, $pdo);  // first step

                // second step : resolve like an user posted a form
                $task = $this->workflows->getCurrentTask($wfAnnualReview, $pdo);
                $task->getNode()->getType()->getHandler()->resolve(array(
                        'user_target_id' => $consultant->getId(),
                        'next_date'   => $task->findNextWorkingDay(
                            (int) $task->calculateDate($consultant->getContractBeginDate(), '+1 year', 'U')
                        ),
                        'workflow' => array(
                            'name'           => sprintf('%s %s', $this->translator->trans('annual_review'), $consultant->getLongName()),
                            'description'    => sprintf('%s %s', $this->translator->trans('annual_review'), $consultant->getLongName())
                        )
                    ), $task, $request, $pdo
                );
            }

            $pdo->commit();

            return true;

        } catch (\Exception $e) {
            $pdo->rollback();

            if ($this->debug) {
                throw $e;
            }

            $this->logger->err($e->getMessage());
            $this->notifier->add(
                'error', 'consultant.admin.notifications.error'
            );

            return false;
        }
    }
}
