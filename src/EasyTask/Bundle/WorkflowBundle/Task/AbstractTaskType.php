<?php

namespace EasyTask\Bundle\WorkflowBundle\Task;

/**
 * abstract task type, defines Task type service structure
 */
abstract class AbstractTaskType
{
    /**
     * abstract method which has to return current state related controller
     * @example return 'EasyTaskDemoBundle:SayHello:meetingAction'
     * @return string
     */
    abstract public function getCurrentState();

}
