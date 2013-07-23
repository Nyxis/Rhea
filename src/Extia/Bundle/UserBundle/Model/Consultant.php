<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BaseConsultant;

class Consultant extends BaseConsultant
{
    protected $currentMission = false;

    const STATUS_PLACED   = 'placed';
    const STATUS_IC       = 'ic';
    const STATUS_RESIGNED = 'resigned';

    /**
     * returns consultant status
     * @return string
     */
    public function getStatus()
    {
        $contractEndDate = $this->getContractEndDate();
        if (!empty($contractEndDate)) {
            return self::STATUS_RESIGNED;
        }

        $currentMission = $this->getCurrentMission();

        return empty($currentMission) ? self::STATUS_IC : self::STATUS_PLACED;
    }

    /**
     * selects and returns current mission
     * @param  Pdo     $con option db connection
     * @return Mission
     */
    public function getCurrentMission(\Pdo $con = null)
    {
        if ($this->currentMission !== false) {
            return $this->currentMission;
        }

        $this->currentMission = null;

        $missions = $this->getConsultantMissions(
            ConsultantMissionQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterByEndDate(null, \Criteria::ISNULL)
                ->orderBybeginDate(\Criteria::DESC)
        );

        if (!$missions->isEmpty()) { // no more selects
            $this->currentMission = $missions->getFirst();
        }

        return $this->currentMission;
    }
}
