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
        return $this
            ->setModelAlias('c')
            ->innerJoin('c.MissionOrder cm')
            ->innerJoin('cm.Mission m')

            ->condition('crh', 'c.CrhId = ?', $internal->getId())

            ->condition('current_mission', 'cm.Current = ?', true)
            ->condition('manager', 'm.ManagerId = ?', $internal->getId())
            ->combine(array('current_mission', 'manager'), 'and', 'current_manager')

            ->where(array('crh', 'current_manager'), 'or')
        ;
    }

}
