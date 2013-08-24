<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BaseConsultant;

class Consultant extends BaseConsultant
{
    protected $currentMissionOrder = false;

    const STATUS_PLACED    = 'placed';
    const STATUS_IC        = 'ic';
    const STATUS_RECRUITED = 'recruited';
    const STATUS_RESIGNED  = 'resigned';

    /**
     * returns consultant status
     * @return string
     */
    public function getStatus()
    {
        // $contractEndDate = $this->getContractEndDate();
        // if (!empty($contractEndDate)) {
        //     return self::STATUS_RESIGNED;
        // }

        $currentMission = $this->getCurrentMission();

        if ($currentMission->getType() == 'ic') {
            return self::STATUS_IC;
        }

        if ($currentMission->getType() == 'waiting') {
            return self::STATUS_RECRUITED;
        }

        return self::STATUS_PLACED;
    }

    /**
     * selects and returns current mission
     *
     * @param  \Pdo    $con option db connection
     * @return Mission
     */
    public function getCurrentMission(\Pdo $con = null)
    {
        return $this->getCurrentMissionOrder($con)->getMission();
    }

    /**
     * select and return current mission order
     *
     * @param  \Pdo         $con
     * @return MissionOrder
     */
    public function getCurrentMissionOrder(\Pdo $con = null)
    {
        if ($this->currentMissionOrder !== false) {
            return $this->currentMissionOrder;
        }

        $this->currentMissionOrder = null;

        $missionsOrders = $this->getMissionOrders(
            MissionOrderQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterByCurrent(true)
                ->filterByEndDate(null, \Criteria::ISNULL)
                ->orderByBeginDate(\Criteria::DESC)
                ->joinWith('Mission')
                ->joinWith('Mission.Manager')
        );

        if (!$missionsOrders->isEmpty()) { // no more selects
            $this->currentMissionOrder = $missionsOrders->getFirst();
        }

        return $this->currentMissionOrder;
    }

    /**
     * returns consultant current manager (function of mission)
     * @param  Pdo          $con option db connection
     * @return Manager|null
     */
    public function getManager(\Pdo $con = null)
    {
        return $this->getCurrentMission($con)->getManager();
    }
}
