<?php

namespace Extia\Bundle\UserBundle\Domain;

use Extia\Bundle\UserBundle\Model\MissionOrder;
use Extia\Bundle\UserBundle\Model\MissionOrderQuery;
use Extia\Bundle\UserBundle\Bridge\MissionMonitoringBridge;

use \DateTime;

/**
 * MissionOrder domain, repository of mission logic
 *
 * @see ExtiaUserBundle/Resources/config/domains.xml
 */
class MissionOrderDomain
{
    protected $missionMonitoringBridge;

    /**
     * construct
     */
    public function __construct(MissionMonitoringBridge $missionMonitoringBridge)
    {
        $this->missionMonitoringBridge = $missionMonitoringBridge;
    }

    /**
     * synchronize all mission order on given date
     *   - ends missions which expires at given date - 1 day
     *       - close old mission_monitoring workflow
     *
     *   - begin mission which starts at given date
     *       - start next mission_monitoring workflow
     *       - calculate consultants manager
     *
     * @param  DateTime $date
     * @param  Pdo      $pdo
     * @return array    report of impacted mission orders and tasks
     */
    public function synchronize(DateTime $date, \Pdo $pdo = null)
    {
        $return = array(
            'activated_mission_orders'    => 0,
            'disactivated_mission_orders' => 0,
            'opened_mission_monitoring'   => 0,
            'closed_mission_monitoring'   => 0,
        );

        if (empty($pdo)) {
            $pdo = \Propel::getConnection('default');
        }

        $pdo->beginTransaction();

        try {
            // retrieve expiring missions
            $expiringMissionOrders = MissionOrderQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->joinWith('Consultant')
                ->joinWith('Mission')
                ->joinWith('Mission.Manager')

                ->filterByEndDate(array('max' => '-1 day'))
                ->filterByCurrent(true)

                ->find($pdo)
            ;

            foreach ($expiringMissionOrders as $missionOrder) {
                $report = $this->close($missionOrder, $pdo);
                $return['disactivated_mission_orders']++;
                $return['closed_mission_monitoring'] += $report['closed_mission_monitoring'];
            }

            // beginning missions
            $beginningMissionOrders = MissionOrderQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->joinWith('Consultant')
                ->joinWith('Mission')
                ->joinWith('Mission.Manager')

                ->filterByBeginDate(strtotime(date('Y-m-d')))
                ->filterByCurrent(false)

                ->find($pdo)
            ;

            foreach ($beginningMissionOrders as $missionOrder) {
                $report = $this->start($missionOrder, $pdo);
                $return['activated_mission_orders']++;
                $return['opened_mission_monitoring'] += $report['opened_mission_monitoring'];
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollback();

            throw $e;
        }

        return $return;
    }

    /**
     * close given mission order
     * triggers mission_monitoring closing on related consultant
     *
     * @param  MissionOrder $missionOrder
     * @param  Pdo          $pdo
     * @return array
     */
    public function close(MissionOrder $missionOrder, \Pdo $pdo = null)
    {
        $return = array('closed_mission_monitoring' => 0);

        // disactivate
        $missionOrder->setCurrent(false);
        $missionOrder->save($pdo);

        // close monitoring
        $return['closed_mission_monitoring'] = $this->missionMonitoringBridge->closeMonitorings(
            $missionOrder->getConsultant(),
            $pdo
        );

        return $return;
    }

    /**
     * start given mission order
     * triggers mission_monitoring creation for related consultant
     *
     * @param  MissionOrder $missionOrder
     * @param  Pdo          $pdo
     * @return array
     */
    public function start(MissionOrder $missionOrder, \Pdo $pdo = null)
    {
        $return = array('opened_mission_monitoring' => 0);

        // activate
        $missionOrder->setCurrent(true);
        $missionOrder->save($pdo);

        // open mission_monitoring only if external mission (no ic)
        if ($missionOrder->getMission()->isExternal()) {
            $consultant = $missionOrder->getConsultant();

            // updates manager id (only if external : on back to internal mission, manager doesnt change)
            $consultant->setManagerId(
                $missionOrder->getMission()->getManagerId()
            );

            $return['opened_mission_monitoring'] = $this->missionMonitoringBridge->createMonitoring(
                $consultant, $pdo
            );

            $consultant->save($pdo);
        }

        return $return;
    }
}
