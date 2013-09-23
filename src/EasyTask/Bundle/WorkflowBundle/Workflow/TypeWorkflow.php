<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;
use EasyTask\Bundle\WorkflowBundle\Event\WorkflowEvent;
use EasyTask\Bundle\WorkflowBundle\Event\WorkflowEvents;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * basic class for workflow type
 * holds all nodes into a ParameterBag node_name => NodeClass
 * @see WorkflowBundle/Resources/config/workflow.xml
 */
class TypeWorkflow implements TypeWorkflowInterface
{
    protected $eventDispatcher;
    protected $nodeBag;
    protected $bootstrapNode;

    /**
     * construct
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->nodeBag         = new ParameterBag(array());
    }

    /**
     * @see TypeWorkflowInterface::addNode()
     */
    public function addNode($nodeName, TypeNodeControllerInterface $nodeType)
    {
        $this->nodeBag->set($nodeName, $nodeType);
    }

    /**
     * @see TypeWorkflowInterface::getNode()
     */
    public function getNode($nodeName)
    {
        return $this->nodeBag->get($nodeName);
    }

    /**
     * @see TypeWorkflowInterface::getNode()
     */
    public function getNodes()
    {
        return $this->nodeBag;
    }

    /**
     * @see TypeWorkflowInterface::setBootstrapNode()
     */
    public function setBootstrapNode($nodeName)
    {
        $this->bootstrapNode = $nodeName;
    }

    /**
     * @see TypeWorkflowInterface::boot()
     */
    public function boot(Workflow $workflow, array $parameters = array(), \Pdo $connection = null)
    {
        $wfEvent = new WorkflowEvent($workflow, $connection);
        $this->eventDispatcher->dispatch(WorkflowEvents::WF_BOOT, $wfEvent);

        return $this->nodeBag
            ->get($this->bootstrapNode)                   // retrieve boot node
            ->notify($workflow, $parameters, $connection) // notify it
        ;
    }
}
