<?php

namespace EasyTask\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\Routing\Route;

use Symfony\Component\DependencyInjection\Container;
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

        $wfContainer     = $container->getDefinition('easy_task.workflows_container');
        $wfRoutingLoader = $container->getDefinition('easy_task_workflow.node_routing_loader');

        foreach ($wfConfigurations as $wfName => $wfConfig) {
            if (empty($wfConfig['nodes'])) {
                throw new \InvalidArgumentException('You have to provide at least one node for workflow "'.$wfName.'"');
            }

            $wfService = null;

            // provided a custom service
            if (!empty($wfConfig['id'])) {
                $wfServiceId = $wfConfig['id'];
                $wfService   = new Reference($wfServiceId);
            }
            // dynamic definition creation from default one
            else {
                $wfServiceId = 'easy_task.workflow.'.$wfName;
                $wfService   = clone $container->getDefinition('easy_task.type_workflow');

                $wfService->setClass(
                    empty($wfConfig['class']) ?
                        $container->getParameter('easy_task.type_workflow.class') :
                        $wfConfig['class']
                );

                $container->setDefinition($wfServiceId, $wfService);
            }

            // injects wf into aggregator
            $wfContainer->addMethodCall('addWorkflow', array($wfName, $wfService));

            // bootstrap node detection
            $nodeNames     = array_keys($wfConfig['nodes']);
            $bootstrapNode = array_shift($nodeNames); // first by default

            // all wf nodes
            foreach ($wfConfig['nodes'] as $nodeName => $nodeConfig) {
                if (empty($nodeConfig['controller']['class']) && empty($nodeConfig['controller']['id'])) {
                    throw new \InvalidArgumentException('You have to provide at least a service id or a class for node controller "'.$nodeName.'"');
                }

                $nodeController = null;
                $nodeControllerConfig = $nodeConfig['controller'];

                // provided a custom service
                if (!empty($nodeControllerConfig['id'])) {
                    $nodeControllerId = $nodeControllerConfig['id'];
                    $nodeController   = new Reference($nodeControllerId);
                }
                // dynamic definition creation from default one
                else {
                    $nodeController   = clone $container->getDefinition('easy_task.type_node');
                    $nodeControllerId = 'easy_task.workflow.'.$wfName.'.node.'.$nodeName;

                    if (!empty($nodeControllerConfig['class'])) {
                        $nodeController->setClass($nodeControllerConfig['class']);
                    }

                    // create new service definition
                    $container->setDefinition($nodeControllerId, $nodeController);
                }

                $nodeControllerDefinition = $container->getDefinition($nodeControllerId);

                // set his name
                $nodeControllerDefinition->addMethodCall('setName', array($nodeName));

                // register actions
                $registeredActions = array();
                $configActions     = empty($nodeControllerConfig['actions']) ? $wfConfig['actions'] : $nodeControllerConfig['actions'];
                foreach ($configActions as $key => $action) {
                    $registeredActions[$key] = $nodeControllerId.':'.$action;
                }

                $nodeControllerDefinition->addMethodCall('registerActions', array($registeredActions));

                // routing
                $nodeRouteConfig = array_replace_recursive(
                    array(
                        'name' => sprintf('EasyTaskWorkflow_%s_%s',
                            Container::camelize($wfName),
                            Container::camelize($nodeName)
                        )
                    ),
                    $nodeConfig['route']
                );

                $wfRoutingLoader->addMethodCall('setNodeRouting', array(
                    $wfName.'.'.$nodeName,
                    $nodeRouteConfig,
                    empty($wfConfig['routes']) ? array() : $wfConfig['routes']
                ));

                $nodeControllerDefinition->addMethodCall('setRoute', array($nodeRouteConfig['name']));

                // first node detection
                if (!empty($nodeConfig['bootstrap'])) {
                    $bootstrapNode = $nodeName;
                }

                // injects node into wf
                $container->getDefinition($wfServiceId)
                    ->addMethodCall('addNode', array($nodeName, $nodeController));
            }

            // injects bootstrap into wf
            $container->getDefinition($wfServiceId)
                ->addMethodCall('setBootstrapNode', array($bootstrapNode));
        }

        $container->getParameterBag()->remove('easy_task.workflows');
    }
}
