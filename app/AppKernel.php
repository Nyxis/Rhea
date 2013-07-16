<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // Propel
            new Propel\PropelBundle\PropelBundle(),
            new Glorpen\Propel\PropelBundle\GlorpenPropelBundle(),

            // Tools bundles
            new Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Ornicar\GravatarBundle\OrnicarGravatarBundle(),

            // Lib bundles
            new Extia\Bundle\UserBundle\ExtiaUserBundle(),
            new EasyTask\Bundle\LogBundle\EasyTaskLogBundle(),

            // Workflow
            new EasyTask\Bundle\WorkflowBundle\EasyTaskWorkflowBundle(),
            new Extia\Bundle\ExtraWorkflowBundle\ExtiaExtraWorkflowBundle(),

            // Display bundles
            new Extia\Bundle\DashboardBundle\ExtiaDashboardBundle(),
            new Extia\Bundle\CommentBundle\ExtiaCommentBundle(),
            new Extia\Bundle\SearchBundle\ExtiaSearchBundle(),

            // Workflows
            new Extia\Workflow\CrhMonitoringBundle\ExtiaWorkflowCrhMonitoringBundle(),

            new Extia\Bundle\FrontBundle\ExtiaFrontBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new EasyTask\Bundle\DemoBundle\EasyTaskDemoBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
