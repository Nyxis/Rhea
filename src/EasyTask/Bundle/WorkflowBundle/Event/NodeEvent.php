<?php

namespace EasyTask\Bundle\WorkflowBundle\Event;

use EasyTask\Bundle\WorkflowBundle\Model\WorkflowNode;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

/**
 * Event class for nodes management
 */
class NodeEvent extends WorkflowEvent
{
    protected $node;

    /**
     * construct
     * @param Workflow $workflow   current workflow
     * @param Pdo      $connection optionnal database connection
     */
    public function __construct(WorkflowNode $node, Workflow $workflow, \Pdo $connection = null)
    {
        parent::__construct($workflow, $connection);

        $this->node = $node;
    }

    /**
     * return event node
     * @return WorkflowNode
     */
    public function getNode()
    {
        return $this->node;
    }
}
