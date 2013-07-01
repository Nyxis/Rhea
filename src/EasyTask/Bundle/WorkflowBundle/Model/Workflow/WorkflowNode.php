<?php

namespace EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\om\BaseWorkflowNode;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Workflow node class, implements ContainerAware to get node type controller
 * in this model for easy management into app
 * @see https://github.com/glorpen/GlorpenPropelBundle#containerawareinterface-for-model
 */
class WorkflowNode extends BaseWorkflowNode implements ContainerAwareInterface
{
    protected $nodeType;
    private $workflowsAggregator;

    /**
     * @see ContainerAwareInterface::setContainer()
     */
    public function setContainer(ContainerInterface $container = null)
    {
        if ($container) {
            $this->workflowsAggregator = $container->get('workflows');
        }
    }

    /**
     * return workflow node type
     * @return TypeNodeControllerInterface
     */
    public function getType()
    {
        if (!empty($this->nodeType)) {
            return $this->nodeType;
        }

        $this->nodeType = $this->workflowsAggregator->getNode(
            $this->getWorkflow(), $this->getName()
        );

        return $this->nodeType;
    }

    /**
     * tests if this node is first
     * @return boolean
     */
    public function isFirst()
    {
        return null === $this->getPrevId();
    }
}
