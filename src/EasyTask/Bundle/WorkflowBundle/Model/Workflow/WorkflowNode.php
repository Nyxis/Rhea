<?php

namespace EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\om\BaseWorkflowNode;

class WorkflowNode extends BaseWorkflowNode
{
    /**
     * tests if this node is first
     * @return boolean
     */
    public function isFirst()
    {
        return null === $this->getPrevId();
    }
}
