<?php

namespace EasyTask\Bundle\WorkflowBundle\Event;

use EasyTask\Bundle\WorkflowBundle\Model\WorkflowNode;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow;
use Symfony\Component\HttpFoundation\Request;

/**
 * Event class for nodes management
 */
class NodeEvent extends WorkflowEvent
{
    protected $node;

    /**
     * construct
     * @param Workflow $workflow   current workflow
     * @param Request  $request    current request
     * @param Pdo      $connection optionnal database connection
     */
    public function __construct(WorkflowNode $node, Workflow $workflow, Request $request, \Pdo $connection = null)
    {
        parent::__construct($workflow, $request, $connection);

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
