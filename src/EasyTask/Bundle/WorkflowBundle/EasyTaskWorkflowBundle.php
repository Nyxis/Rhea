<?php

namespace EasyTask\Bundle\WorkflowBundle;

use EasyTask\Bundle\WorkflowBundle\DependencyInjection\Compiler\WorkflowAggregatorCompilerPass;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EasyTaskWorkflowBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new WorkflowAggregatorCompilerPass());
    }
}
