<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BaseInternalQuery;

class InternalQuery extends BaseInternalQuery
{
    private $isCountingTasks;

    /**
     * adds count fields for pas tasks
     * @return InternalQuery
     */
    public function selectCountTasks()
    {
        $this->isCountingTasks = true;

        return $this
            ->leftJoin('Person.TaskRelatedByAssignedTo')
            ->leftJoin('TaskRelatedByAssignedTo.Node')

            ->withColumn('SUM(if(task.completion_date < "'.date('Y-m-d H:i:s').'" AND workflow_node.current = 1, 1, 0))', 'nbPastTasks')
            ->withColumn('SUM(if(workflow_node.current = 1, 1, 0))', 'nbActiveTasks')
        ;
    }

    /**
     * count on related consultants number
     * @param  string        $dir
     * @return InternalQuery
     */
    public function orderByNbPastTasks($dir = \Criteria::ASC)
    {
        return $this->_if(empty($this->isCountingTasks))
                ->selectCountTasks()
            ->_endif()
            ->orderBy('nbPastTasks', $dir)
        ;
    }

    /**
     * performs a filter on given name
     * @param  string        $name
     * @return InternalQuery
     */
    public function filterByName($name)
    {
        return $this->filterByFirstname('%'.$name.'%', \Criteria::LIKE)
            ->_or()->filterByLastname('%'.$name.'%', \Criteria::LIKE)
            ->_or()->filterByTrigram('%'.$name.'%', \Criteria::LIKE)
        ;
    }

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
            ->endUse()
        ;
    }
}
