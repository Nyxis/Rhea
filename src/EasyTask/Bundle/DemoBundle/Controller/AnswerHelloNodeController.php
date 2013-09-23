<?php

namespace EasyTask\Bundle\DemoBundle\Controller;

use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

// use EasyTask\Bundle\WorkflowBundle\Workflow\TypeNodeController;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;

/**
 * AnswerHello workflow node controller
 * @see EasyTask\Bundle\WorkflowBundle\Workflow\TypeNodeController
 */
class AnswerHelloNodeController extends TypeNodeController
{
    public function nodeAction(Request $request)
    {

    }
}
