<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

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
     * defines node routing
     * @param string $route
     */
    public function setRoute($route);

    /**
     * return node name
     * @return string
     */
    public function getName();

    /**
     * return routes on which have to redirect on to execute state
     * @return string
     */
    public function getRoute();

    /**
     * defines supported actions on node controller
     * @param array $actions
     */
    public function registerActions(array $actions);

    /**
     * tests if given action is supproted
     * @param  string $actionName
     * @return bool
     */
    public function supportsAction($actionName);

    /**
     * returns requested action if exists
     * @param  string $actionName
     * @return string
     */
    public function getAction($actionName);

    /**
     * methods which is call when the next() method has been called on a Worklow object
     * has to return a Response or null for redirecting on default
     *
     * @param  Workflow $workflow
     * @param  array    $parameters optionnal list of parameters to give to next task notification
     * @param  Pdo      $connection optionnal database connection used to create nodes
     * @return Response
     */
    public function notify(Workflow $workflow, array $parameters = array(), \Pdo $connection = null);
}
