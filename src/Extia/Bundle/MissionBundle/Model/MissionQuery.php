<?php

namespace Extia\Bundle\MissionBundle\Model;

use Extia\Bundle\MissionBundle\Model\om\BaseMissionQuery;

class MissionQuery extends BaseMissionQuery
{
    /**
     * order on related client name column
     * @param  string       $dir sort direction
     * @return MissionQuery
     */
    public function orderByClientName($dir = \Criteria::ASC)
    {
        return $this->useClientQuery()
                ->orderByTitle($dir)
            ->endUse()
        ;
    }

    /**
     * order on related client name column
     * @param  string       $dir sort direction
     * @return MissionQuery
     */
    public function orderByManager($dir = \Criteria::ASC)
    {
        return $this->useManagerQuery()
                ->orderByLastname($dir)
                ->orderByFirstname($dir)
            ->endUse()
        ;
    }

    /**
     * order on related client name column
     * @param  string       $dir sort direction
     * @return MissionQuery
     */
    public function orderByNbConsultants($dir = \Criteria::ASC)
    {
        return $this->orderBy('NbConsultants', $dir);
    }

    /**
     * order on related client name column
     * @param  string       $dir sort direction
     * @return MissionQuery
     */
    public function orderByContact($dir = \Criteria::ASC)
    {
        return $this->orderByContactName($dir);
    }


}
