<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BaseInternalQuery;

class InternalQuery extends BaseInternalQuery
{
    /**
     * filter internal query on active status : when any resign
     * @return InternalQuery
     */
    public function filterByActive()
    {
        return $this->filterByResignationId(null, \Criteria::ISNULL);
    }

    /**
     * filter internal query on inactive status : when resigned
     * @return InternalQuery
     */
    public function filterByInactive()
    {
        return $this->filterByResignationId(null, \Criteria::ISNOTNULL);
    }

    /**
     * filter internal query on user type
     * @return InternalQuery
     */
    public function filterByType($type)
    {
        return $this->usePersonTypeQuery()
                ->filterByCode((array) $type)
            ->endUse();
    }
}
