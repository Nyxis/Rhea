<?php

namespace EasyTask\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * compiler pass to catch all Workflow services under tag "easy_task.task_type"
 */
class WorkflowAggregatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $wfConfigurations = $container->getParameter('easy_task.workflows');

        if (empty($wfConfigurations)
            || false === $container->hasDefinition('easy_task.workflows_container')
            || false === $container->hasDefinition('easy_task.type_node')
            || false === $container->hasDefinition('easy_task.type_workflow')) {
            return;
        }

        $wfContainer = $container->getDefinition('easy_task.workflows_container');

        foreach ($wfConfigurations as $wfName => $wfConfig) {
            if (empty($wfConfig['nodes'])) {
                throw new \InvalidArgumentException('You have to provide at least one node for workflow "'.$wfName.'"');
            }

            $wfService = null;
            if (!empty($wfConfig['id'])) {
                $wfServiceId = $wfConfig['id'];
                $wfService   = new Reference($wfServiceId);
            } else {
                $wfServiceId = 'easy_task.type_workflow.'.$wfName;
                $wfService   = clone $container->getDefinition('easy_task.type_workflow');

                $wfService->setClass(empty($wfConfig['class']) ?
                    $container->getParameter('easy_task.type_workflow.class') :
                    $wfConfig['class']);

                $container->setDefinition($wfServiceId, $wfService);
            }

            // injects wf into aggregator
            $wfContainer->addMethodCall('addWorkflow', array($wfName, $wfService));

            // bootstrap node detection
            $nodeNames     = array_keys($wfConfig['nodes']);
            $bootstrapNode = array_shift($nodeNames);

            // all wf nodes
            foreach ($wfConfig['nodes'] as $nodeName => $nodeConfig) {
                if (empty($nodeConfig['class']) && empty($nodeConfig['id'])) {
                    throw new \InvalidArgumentException('You have to provide at least a service id or a class for node "'.$nodeName.'"');
                }

                // class or id ?
                $nodeService   = null;
                if (!empty($nodeConfig['id'])) {
                    $nodeServiceId = $nodeConfig['id'];
                    $nodeService   = new Reference($nodeConfig['id']);
                } else {
                    $nodeService   = clone $container->getDefinition('easy_task.type_node');
                    $nodeServiceId = 'easy_task.type_node.'.$nodeName;

                    if (!empty($nodeConfig['class'])) {
                        $nodeService->setClass($nodeConfig['class']);
                    }

                    // create new service definition
                    $container->setDefinition($nodeServiceId, $nodeService);
                }

                $nodeServiceDefinition = $container->getDefinition($nodeServiceId);
                $nodeServiceDefinition->addMethodCall('setName', array($nodeName));

                if (!empty($nodeConfig['route'])) {
                    $nodeServiceDefinition->addMethodCall('setRoute', array($nodeConfig['route']));
                }

                if (!empty($nodeConfig['bootstrap'])) {
                    $bootstrapNode = $nodeName;
                }

                // injects node into wf
                $container->getDefinition($wfServiceId)
                    ->addMethodCall('addNode', array($nodeName, $nodeService));
            }

            // injects bootstrap into wf
            $container->getDefinition($wfServiceId)
                ->addMethodCall('setBootstrapNode', array($bootstrapNode));
        }

        $container->getParameterBag()->remove('easy_task.workflows');
    }
}
