<?php

namespace EasyTask\Bundle\WorkflowBundle\Event;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event class for workflow management
 */
class WorkflowEvent extends Event
{
    protected $workflow;
    protected $connection;

    /**
     * construct
     * @param Workflow $workflow   current workflow
     * @param Pdo      $connection optionnal database connection
     */
    public function __construct(Workflow $workflow, \Pdo $connection = null)
    {
        $this->workflow   = $workflow;
        $this->connection = $connection;
    }

    /**
     * return event workflow
     * @return Workflow
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * return event connection, can be null
     * @return Pdo|null
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
