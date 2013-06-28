<?php

namespace Extia\Bundle\ExtraWorkflowBundle\Model\Workflow;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\om\BaseTaskQuery;

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
