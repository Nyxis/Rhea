<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BaseConsultantQuery;

class ConsultantQuery extends BaseConsultantQuery
{
    /**
     * filters current query on internal referer (crh or current manager)
     *
     * @param  Internal        $internal
     * @return ConsultantQuery
     */
    public function filterByInternalReferer(Internal $internal)
    {
        return $this->filterByCrh($internal)
            ->_or()
            ->useConsultantMissionQuery()
                ->useMissionQuery()
                    ->filterByManager($internal)
                ->endUse()
            ->endUse();
    }
}
