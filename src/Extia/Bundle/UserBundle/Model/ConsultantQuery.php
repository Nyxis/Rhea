<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BaseConsultantQuery;

class ConsultantQuery extends BaseConsultantQuery
{
    /**
     * filters query on a manager
     * @param  Internal        $internal
     * @return ConsultantQuery
     */
    public function filterByManager(Internal $internal)
    {
        return $this->filterByManagerId($internal->getId());
    }

    /**
     * filters query on a manager id
     * @param  int             $id
     * @return ConsultantQuery
     */
    public function filterByManagerId($id)
    {
        return $this->useMissionOrderQuery()
                ->filterByCurrent(true)
                ->useMissionQuery()
                    ->filterByManagerId($id)
                ->endUse()
            ->endUse();
    }

    /**
     * filters query on consultant status
     * @param  string          $status
     * @return ConsultantQuery
     */
    public function filterByStatus($status)
    {
        return $this->_if($status == 'resigned')
                ->filterByInactive()
            ->_else()
                ->useMissionOrderQuery()
                    ->filterByCurrent(true)
                    ->useMissionQuery()
                        ->_if($status == 'placed')->filterByType('client')
                        ->_elseif($status == 'ic')->filterByType('ic')
                        ->_elseif($status == 'recruited')->filterByType('recruitement')
                        ->_endif()
                    ->endUse()
                ->endUse()
            ->_endif();
    }

    /**
     * filters query on consultant client (current or not)
     * @param  int             $id
     * @param  bool            $current (default true)
     * @return ConsultantQuery
     */
    public function filterByClient($id, $current = true)
    {
        return $this->useMissionOrderQuery()
                ->filterByCurrent($current)
                ->useMissionQuery()
                    ->filterByClientId($id)
                ->endUse()
            ->endUse();
    }

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

    /**
     * orders query on current mission client name
     * @param  string          $dir
     * @return ConsultantQuery
     */
    public function orderByCurrentMission($dir = \Criteria::ASC)
    {
        return $this->useMissionOrderQuery()
                ->filterByCurrent(true)
                ->useMissionQuery()
                    ->useClientQuery()
                        ->orderByTitle($dir)
                    ->endUse()
                ->endUse()
            ->endUse();
    }

}
