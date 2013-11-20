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

            // Workflow
            new EasyTask\Bundle\WorkflowBundle\EasyTaskWorkflowBundle(),

            // Core
            new Extia\Bundle\TaskBundle\ExtiaTaskBundle(),
            new Extia\Bundle\UserBundle\ExtiaUserBundle(),

            // Other features
            new Extia\Bundle\CommentBundle\ExtiaCommentBundle(),
            new Extia\Bundle\SearchBundle\ExtiaSearchBundle(),
            new Extia\Bundle\ActivityBundle\ExtiaActivityBundle(),
            new Extia\Bundle\MenuBundle\ExtiaMenuBundle(),
            new Extia\Bundle\NotificationBundle\ExtiaNotificationBundle(),
            new Extia\Bundle\GroupBundle\ExtiaGroupBundle(),
            new Extia\Bundle\DocumentBundle\ExtiaDocumentBundle(),
            new Extia\Bundle\MissionBundle\ExtiaMissionBundle(),

            // Workflows
            new Extia\Workflow\CrhMonitoringBundle\ExtiaWorkflowCrhMonitoringBundle(),
            new Extia\Workflow\MissionMonitoringBundle\ExtiaWorkflowMissionMonitoringBundle(),
            new Extia\Workflow\AnnualReviewBundle\ExtiaWorkflowAnnualReviewBundle(),
            new Extia\Workflow\LunchBundle\ExtiaWorkflowLunchBundle()
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
