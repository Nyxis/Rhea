<?php

namespace EasyTask\Bundle\DemoBundle\Controller;

use EasyTask\Bundle\WorkflowBundle\Model\Task;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * sayHello task type controller
 * @see EasyTask\Bundle\WorkflowBundle\Task\AbstractTaskType
 */
class SayHelloController extends Controller
{
    /**
     * meeting state action
     * @param EasyTask\Bundle\WorkflowBundle\Model\Task $task          current task
     * @param string                                    $redirectRoute
     */
    public function stateMeetAction(Task $task, $redirectRoute)
    {
        $this->render('EasyTaskDemoBundle:SayHello:stateMeet', array(

        ));
    }
}
