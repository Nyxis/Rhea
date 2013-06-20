<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;
use Symfony\Component\HttpFoundation\Request;

/**
 * Type Node interface
 */
interface TypeNodeControllerInterface
{
    /**
     * defines node name
     * @param string $name
     */
    public function setName($name);

    /**
     * defines routes on which have to redirect on to execute state
     * @param string $route
     */
    public function setRoute($route);

    /**
     * methods which is call when the next() method has been called on a Worklow object
     * has to return a Response or null for redirecting on default
     *
     * @param  Workflow $workflow
     * @param  Request  $request
     * @param  Pdo      $connection optionnal database connection used to create nodes
     * @return Response
     */
    public function notify(Workflow $workflow, Request $request, \Pdo $connection = null);
}
