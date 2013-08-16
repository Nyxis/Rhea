<?php

namespace Extia\Bundle\TaskBundle\Model;

use Extia\Bundle\TaskBundle\Model\om\BaseTaskQuery;

class TaskQuery extends BaseTaskQuery
{
    /**
     * adds joins and filter on workflow node table
     * @return TaskQuery
     */
    public function joinWithCurrentNodes()
    {
        return $this->useNodeQuery()
                ->filterByCurrent(true)
                ->filterByEnded(false)
            ->endUse()
            ->joinWith('Node')
            ->joinWith('Node.Workflow');
    }

    /**
     * filter query on given workflow types
     * @param  array     $workflowTypes
     * @return TaskQuery
     */
    public function filterByWorkflowTypes(array $workflowTypes)
    {
        return $this->useNodeQuery()
            ->useWorkflowQuery()
                ->filterByType($workflowTypes)
            ->endUse()
        ->endUse();
    }

    /**
     * performs target user joins for tasks
     * @param  string    locale optionnal locale
     * @return TaskQuery
     */
    public function joinWithTargettedUser($locale = null)
    {
        return $this->joinWith('UserTarget', \Criteria::LEFT_JOIN)
            ->joinWith('UserTarget.Job')
            ->useUserTargetQuery(null, \Criteria::LEFT_JOIN)
                ->useJobQuery()
                    ->_if(empty($locale))->joinWithI18n()
                    ->_else()->joinWithI18n($locale)
                    ->_endif()
                ->endUse()
            ->endUse();
    }

    /**
     * performs all used joins for tasks
     * @param  string    locale optionnal locale
     * @return TaskQuery
     */
    public function joinWithAll($locale = null)
    {
        return $this->joinWith('Node')
            ->joinWith('Node.Workflow')
            ->joinWithTargettedUser($locale)
            ->joinWith('Comment', \Criteria::LEFT_JOIN);
    }
}
