<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\UserBundle\Model\MissionOrder;

use Extia\Bundle\MissionBundle\Model\MissionQuery;
use Extia\Bundle\MissionBundle\Model\ClientQuery;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * consultant mission switching handler
 *
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class ChangeMissionHandler
{
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
        if (!$form->isValid()) {
            // notifier
            return false;
        }

        $switchMissionData = $form->getData();
        $currentEndDate    = $switchMissionData['end_date'];

        $pdo = \Propel::getConnection('default');
        $pdo->beginTransaction();

        try {

            // close current mission
            $currentMissionOrder = $consultant->getCurrentMissionOrder($pdo);
            $currentMissionOrder->setCurrent(false);
            $currentMissionOrder->setEndDate($currentEndDate);
            $currentMissionOrder->save($pdo);

            // open next
            $nextMissionOrder = new MissionOrder();
            $nextMissionOrder->setConsultant($consultant);
            $nextMissionOrder->setCurrent(true);

            if (empty($switchMissionData['next_intercontract'])) {
                $nextMissionOrder->setBeginDate($switchMissionData['next_begin_date']);
                $nextMissionOrder->setMissionId($switchMissionData['next_mission_id']);
            }
            else {
                $nextMissionOrder->setBeginDate($currentEndDate->format('U') + 3600*24);
                $nextMissionOrder->setMission(
                    $this->getManagerMission($consultant->getManagerId(), 'ic', $pdo)
                );
            }

            $nextMissionOrder->save($pdo);

            // time between current and next mission -> ic
            if (($nextMissionOrder->getBeginDate('U') - $currentMissionOrder->getEndDate('U')) > 48*3600) {

                $icMission = $this->getManagerMission($consultant->getManagerId(), 'ic', $pdo);

                $icMissionOrder = new MissionOrder();
                $icMissionOrder->setMission($icMission);
                $icMissionOrder->setConsultant($consultant);
                $icMissionOrder->setBeginDate($currentMissionOrder->getEndDate());
                $icMissionOrder->setEndDate($nextMissionOrder->getBeginDate());
                $icMissionOrder->save($pdo);
            }

            $consultant->setManagerId($nextMissionOrder->getMission()->getManagerId());
            $consultant->save($pdo);

            $pdo->commit();

            return true;
        } catch (\Exception $e) {
            $pdo->rollback();
            throw $e;
        }

        return false;
    }
}