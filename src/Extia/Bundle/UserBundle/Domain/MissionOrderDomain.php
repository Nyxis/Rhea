<?php

namespace Extia\Bundle\UserBundle\Domain;

use Extia\Bundle\UserBundle\Model\Consultant;
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
     * Assign given mission order to given consultant
     *
     * @param  Consultant               $consultant
     * @param  MissionOrder             $missionOrder
     * @return MissionOrderDomain
     * @throws InvalidArgumentException If given mission order has any mission
     */
    public function assignOrder(Consultant $consultant, MissionOrder $missionOrder, \Pdo $pdo)
    {
        if (!$mission = $missionOrder->getMission()) {
            throw new \InvalidArgumentException('Given mission order has no missions.');
        }

        // @todo import here change mission handler logic
    }

    /**
     * synchronize all or consultant if passed mission orders on given date
     *   - ends missions which expires at given date - 1 day
     *       - close old mission_monitoring workflow
     *
     *   - begin mission which starts at given date
     *       - start next mission_monitoring workflow
     *       - calculate consultants manager
     *
     * @param  DateTime   $date
     * @param  Consultant $consultant opt consultant to only sync
     * @param  Pdo        $pdo
     * @return array      report of impacted mission orders and tasks
     */
    public function synchronize(DateTime $date, Consultant $consultant = null, \Pdo $pdo = null)
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
            $expiringData = clone $date;

            // retrieve expiring missions
            $expiringMissionOrders = MissionOrderQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->joinWith('Consultant')
                ->joinWith('Mission')
                ->joinWith('Mission.Manager')

                ->_if(!empty($consultant))
                    ->filterByConsultant($consultant)
                ->_endif()

                ->filterByEndDate(array('max' => $expiringData->sub(date_interval_create_from_date_string('1 day'))))
                ->filterByCurrent(true)

                ->find($pdo)
            ;

            foreach ($expiringMissionOrders as $missionOrder) {
                $report = $this->close($missionOrder, $pdo);
                $return['disactivated_mission_orders']++;
                $return['closed_mission_monitoring'] += $report['closed_mission_monitoring'];
            }

            // current missions
            $beginningMissionOrders = MissionOrderQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->joinWith('Consultant')
                ->joinWith('Mission')
                ->joinWith('Mission.Manager')

                ->_if(!empty($consultant))
                    ->filterByConsultant($consultant)
                ->_endif()

                ->filterByCurrent(false)

                ->filterByBeginDate(array('max' => $date))

                ->filterByEndDate(array('min' => $date))
                ->_or()
                ->filterByEndDate(null, \Criteria::ISNULL)

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
