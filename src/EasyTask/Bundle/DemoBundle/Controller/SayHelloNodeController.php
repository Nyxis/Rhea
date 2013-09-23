<?php

namespace EasyTask\Bundle\DemoBundle\Controller;

use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

// use EasyTask\Bundle\WorkflowBundle\Workflow\TypeNodeController;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * sayHello workflow node controller
 * @see EasyTask\Bundle\WorkflowBundle\Workflow\TypeNodeController
 */
class SayHelloNodeController extends TypeNodeController
{
    public function nodeAction(Request $request, $workflowId)
    {
        $node     = $this->findWorkflowNode($workflowId);
        $workflow = $node->getWorkflow();

        if ($request->request->has('answer')) {
            $response = $this->get('workflows')
                ->getNode($workflow, 'answer_hello')
                ->notify($workflow, $request);

            if ($response instanceof Response) {
                return $response;
            }
        }

        return $this->render('EasyTaskDemoBundle::say_hello.html.twig', array(
            'node' => $node
        ));
    }
}
