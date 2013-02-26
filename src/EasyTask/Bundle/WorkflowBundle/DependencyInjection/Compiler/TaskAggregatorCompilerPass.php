<?php

namespace EasyTask\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * compiler pass to catch all task services under tag "easy_task.task_type"
 */
class TaskAggregatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('easy_task.aggregator')) {
            return;
        }

        $definition = $container->getDefinition('easy_task.aggregator');

        foreach ($container->findTaggedServiceIds('easy_task.task') as $id => $attributes) {
            $definition->addMethodCall('addTask', array(
                $id,
                new Reference($id)
            ));
        }
    }
}
