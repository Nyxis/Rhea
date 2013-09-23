<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\UserBundle\Model\MissionOrder;
use Extia\Bundle\UserBundle\Model\MissionOrderQuery;

use Extia\Bundle\UserBundle\Domain\MissionOrderDomain;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * consultant mission switching handler
 *
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class ChangeMissionHandler extends AdminHandler
{
    protected $missionOrderDomain;

    /**
     * construct
     * @param MissionOrderDomain $missionOrderDomain [description]
     */
    public function __construct(MissionOrderDomain $missionOrderDomain)
    {
        $this->missionOrderDomain = $missionOrderDomain;
    }

    /**
     * tests if form is valid for this handler
     *
     * @param  Form    $form
     * @return boolean
     */
    public function isValid(Form $form)
    {
        $return = true;

        if (!$form->isValid()) {
            $return = false;
        }

        // any fields required if no ic
        if (!$form->get('next_intercontract')->getData()) {

            // current end must be greater than next begin
            $errorReport = $this->validator->validateValue(
                $form->get('next_begin_date')->getData(), array(
                    new Assert\NotBlank(), new Assert\GreaterThan(array(
                        'value'   => $form->get('end_date')->getData(),
                        'message' => $this->translator->trans('consultant.change_mission.validation.begin_before_end')
                    ))
                )
            );

            $return = $this->storeErrors($form->get('next_begin_date'), $errorReport) < 1 && $return;
        }

        return $return;
    }

    /**
     * handle method
     *
     * @param  Request    $request
     * @param  Form       $form
     * @param  Consultant $consultant
     * @return bool
     */
    public function handle(Request $request, Form $form, Consultant $consultant)
    {
        $form->submit($request);
        if (!$this->isValid($form)) {
            $this->notifier->add('warning', 'consultant.change_mission.notifications.invalid_form');

            return false;
        }

        $switchMissionData = $form->getData();
        $currentEndDate    = $switchMissionData['end_date'];

        $pdo = \Propel::getConnection('default');
        $pdo->beginTransaction();

        try {

            // end current mission
            $currentMissionOrder = $consultant->getCurrentMissionOrder($pdo);
            $currentMissionOrder->setEndDate($currentEndDate);

            // delete next missions
            MissionOrderQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterByConsultantId($consultant->getId())
                ->filterByBeginDate(array('min' => $currentMissionOrder->getBeginDate()))
                ->filterByCurrent(false)
                ->delete($pdo)
            ;

            $currentMissionOrder->save($pdo);

            // open next
            $nextMissionOrder = new MissionOrder();
            $nextMissionOrder->setConsultant($consultant);

            if (empty($switchMissionData['next_intercontract'])) {
                $nextMissionOrder->setBeginDate($switchMissionData['next_begin_date']);
                $nextMissionOrder->setMissionId($switchMissionData['next_mission_id']);
            } else {
                $nextMissionOrder->setBeginDate($currentEndDate->format('U') + 3600*24);
                $nextMissionOrder->setMission(
                    $this->getManagerMission($consultant->getManagerId(), 'ic', $pdo)
                );
            }

            $nextMissionOrder->save($pdo);

            // time between current and next mission -> ic
            if (($nextMissionOrder->getBeginDate('U') - $currentMissionOrder->getEndDate('U')) > 72*3600) {

                $icMission = $this->getManagerMission($consultant->getManagerId(), 'ic', $pdo);

                $icMissionOrder = new MissionOrder();
                $icMissionOrder->setMission($icMission);
                $icMissionOrder->setConsultant($consultant);
                $icMissionOrder->setBeginDate($currentMissionOrder->getEndDate());
                $icMissionOrder->setEndDate($nextMissionOrder->getBeginDate('U') - 3600*24);
                $icMissionOrder->save($pdo);

                $nextMissionOrder = $icMissionOrder;
            }

            // triggers mission switching if next mission begins today
            if ($nextMissionOrder->getBeginDate('Y-m-d') == date('Y-m-d')) {

                // close current
                $this->missionOrderDomain->close($currentMissionOrder, $pdo);

                // make next current
                $this->missionOrderDomain->start($nextMissionOrder, $pdo);
            }

            $pdo->commit();

            return true;
        } catch (\Exception $e) {
            $pdo->rollback();
            throw $e;
        }

        return false;
    }
}
