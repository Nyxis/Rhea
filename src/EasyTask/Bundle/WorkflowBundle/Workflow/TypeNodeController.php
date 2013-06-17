<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Type Node class
 * Copied into service for each defined nodes
 */
class TypeNodeController extends Controller implements TypeNodeControllerInterface
{
    protected $routeNode;
    protected $name;

    /**
     * @see TypeNodeControllerInterface:setRoute()
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @see TypeNodeControllerInterface:setRoute()
     */
    public function setRoute($route)
    {
        $this->routeNode = $route;
    }

    /**
     * @see TypeNodeControllerInterface:notify()
     */
    public function notify(Request $request, Workflow $workflow)
    {
        if (empty($this->routeNode)) {
            throw new \RuntimeException(sprintf('Without define "route" config on "%s" node, we cannot know where redirect on.
                You have to define your own notify() method into a custom controller using "class" key or define a "route" key otherwise.', $this->name));
        }

        return $this->redirect($this->get('router')->generate($this->route, array(
            'workflow' => $workflow->getId()
        )));
    }
}
