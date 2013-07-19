<?php

namespace EasyTask\Bundle\WorkflowBundle\Event;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event class for workflow management
 */
class WorkflowEvent extends Event
{
    protected $workflow;
    protected $request;
    protected $connection;

    /**
     * construct
     * @param Workflow $workflow   current workflow
     * @param Request  $request    current request
     * @param Pdo      $connection optionnal database connection
     */
    public function __construct(Workflow $workflow, Request $request, \Pdo $connection = null)
    {
        $this->workflow   = $workflow;
        $this->request    = $request;
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
     * return event request
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
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
