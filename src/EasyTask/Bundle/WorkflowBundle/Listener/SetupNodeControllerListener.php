<?php

namespace EasyTask\Bundle\WorkflowBundle\Listener;

use EasyTask\Bundle\WorkflowBundle\Workflow\Aggregator;
use EasyTask\Bundle\WorkflowBundle\Workflow\TypeNodeControllerInterface;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

/**
 * Listener used to populate NodeControllers with node data if called on node registered routes
 * @see EasyTask/WorkflowBundle/Resources/config/listeners.xml
 */
class SetupNodeControllerListener
{
    protected $workflows;

    /**
     * construct
     * @param Aggregator $workflows
     */
    public function __construct(Aggregator $workflows)
    {
        $this->workflows = $workflows;
    }

    /**
     * tests if given event is supported
     *
     * @param  FilterControllerEvent $event
     * @return bool
     */
    protected function supports(FilterControllerEvent $event)
    {
        return $event->getRequest()->attributes->has('_node');
    }

    /**
     * event handler method bound on kernel.controller event throught services
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!$this->supports($event)) {
            return;
        }

        $controllers = $event->getController();
        $controller  = $controllers[0]; // controllers are rendered as an array

        $request = $event->getRequest();
        $route   = $request->attributes->get('_route');

        if (!$controller instanceof TypeNodeControllerInterface) {
            throw new \RuntimeException(sprintf('You marked "%s" route as a node route, but "%s" mapped controller doesnt implements TypeNodeControllerInterface. Miss configuration ?',
                $route,
                get_class($controller)
            ));
        }

        // injects manually node parameters into controller (:: routing syntax does not care of DI :s)
        $controller->setRoute($route);
        $controller->setName($request->attributes->get('_node'));
    }

}
