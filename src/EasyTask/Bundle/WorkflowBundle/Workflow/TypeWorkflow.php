<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * basic class for workflow type
 * holds all nodes into a ParameterBag node_name => NodeClass
 * @see WorkflowBundle/Resources/config/workflow.xml
 */
class TypeWorkflow implements TypeWorkflowInterface
{
    protected $nodeBag;
    protected $bootstrapNode;

    /**
     * construct
     */
    public function __construct()
    {
        $this->nodeBag = new ParameterBag(array());
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
     * @see TypeWorkflowInterface::setBootstrapNode()
     */
    public function setBootstrapNode($nodeName)
    {
        $this->bootstrapNode = $nodeName;
    }

    /**
     * @see TypeWorkflowInterface::boot()
     */
    public function boot(Workflow $workflow, \Pdo $connection = null)
    {
        return $this->nodeBag->get($this->bootstrapNode)->notify($workflow, $connection);
    }
}
