<?php

namespace EasyTask\Bundle\DemoBundle\Task;

use EasyTask\Bundle\WorkflowBundle\Task\AbstractTaskType;

/**
 * demo task type, meeting then say hello
 * @see
 */
class SayHelloTaskType extends AbstractTaskType
{
    /**
     *
     * @return string
     */
    public function getCurrentState()
    {
        return 'EasyTaskDemoBundle:SayHello:stateMeetAction';
    }

}
