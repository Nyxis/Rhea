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
}
