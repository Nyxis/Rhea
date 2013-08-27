<?php

namespace EasyTask\Bundle\WorkflowBundle\Routing\Loader;

use EasyTask\Bundle\WorkflowBundle\Workflow\Aggregator;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Routing loader for inject custom node routes
 * @see EasyTask/Bundle/WorkflowBundle/Resources/config/routing.xml
 */
class NodeRoutingLoader implements LoaderInterface
{
    protected $workflows;
    protected $nodeRouting = array();

    private $loaded = false;

    /**
     * __construct
     * @param Aggregator $workflows
     */
    public function __construct(Aggregator $workflows)
    {
        $this->workflows = $workflows;
    }

    /**
     * called by DI from configs
     * @param string $nodeId
     * @param array  $nodeRouting
     * @param array  $wfRouting
     */
    public function setNodeRouting($nodeId, array $nodeRouting, array $wfRouting)
    {
        $this->nodeRouting[$nodeId] = array(
            'node'     => $nodeRouting,
            'workflow' => $wfRouting
        );
    }

    /**
     * @see Symfony\Component\Config\Loader\LoaderInterface::load()
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Cannot load many times workflow nodes routes');
        }

        $routes = new RouteCollection();

        foreach ($this->workflows as $workflowName => $workflow) {
            foreach ($workflow->getNodes() as $nodeName => $node) {

                // any node action, any route
                if (!$node->supportsAction('node')) {
                    continue;
                }

                // any config any route
                if (empty($this->nodeRouting[$workflowName.'.'.$nodeName])) {
                    continue;
                }

                $nodeRouting = $this->nodeRouting[$workflowName.'.'.$nodeName]['node'];
                $wfRouting   = $this->nodeRouting[$workflowName.'.'.$nodeName]['workflow'];

                $pattern = sprintf('%s%s',
                    empty($wfRouting['prefix']) ? '' : $wfRouting['prefix'],
                    $nodeRouting['pattern']
                );

                $defaults     = array('_controller' => $node->getAction('node'));
                $requirements = array();

                $routes->add($nodeRouting['name'], new Route($pattern, $defaults, $requirements));
            }
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * @see Symfony\Component\Config\Loader\LoaderInterface::supports()
     */
    public function supports($resource, $type = null)
    {
        return 'workflow_nodes' === $type;
    }

    /**
     * @see Symfony\Component\Config\Loader\LoaderInterface::getResolver()
     */
    public function getResolver() { }

    /**
     * @see Symfony\Component\Config\Loader\LoaderInterface::setResolver()
     */
    public function setResolver(LoaderResolverInterface $resolver) { }
}
