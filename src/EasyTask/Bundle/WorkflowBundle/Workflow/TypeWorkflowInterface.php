<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;

use Symfony\Component\HttpFoundation\Request;

/**
 * interface to implement into all workflows type classes
 */
interface TypeWorkflowInterface
{
    /**
     * called by DI, adds a new node type for this workflow
     * @param string                      $nodeName
     * @param TypeNodeControllerInterface $nodeType
     */
    public function addNode($nodeName, TypeNodeControllerInterface $nodeType);

    /**
     * return nodes by his alias
     * @param string                      $nodeName
     * @param TypeNodeControllerInterface $nodeType
     */
    public function getNode($nodeName);

    /**
     * return all nodes
     * @return ParameterBag
     */
    public function getNodes();

    /**
     * called by DI, defines which node ah to be call in first place
     * @param string $nodeName
     */
    public function setBootstrapNode($nodeName);

    /**
     * boot method for given Workflow
     * @param Workflow $workflow
     * @param Request  $request
     * @param Pdo      $connection optionnal database connection used for transaction
     */
    public function boot(Workflow $workflow, Request $request, \Pdo $connection = null);
}
