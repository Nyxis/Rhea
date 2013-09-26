<?php

namespace Extia\Workflow\LunchBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;

/**
 * bootstrap workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class LunchController extends TypeNodeController
{
    /**
     * {@inherit_doc}
     */
    public function getHandler()
    {
        return $this->get('lunch.lunch.handler');
    }

    /**
     * {@inherit_doc}
     */
    protected function getTemplates()
    {
        return array(
            'node'             => 'ExtiaWorkflowLunchBundle::node.html.twig',
            'modal'            => 'ExtiaWorkflowLunchBundle:Lunch:modal.html.twig',
            'notification'     => 'ExtiaWorkflowLunchBundle:Lunch:notification.html.twig',
            'timeline_element' => 'ExtiaWorkflowLunchBundle:Lunch:timeline_element.html.twig'
        );
    }

    /**
     * {@inherit_doc}
     */
    protected function executeNode(Request $request, Task $task, $template)
    {
        $form = $this->get('lunch.lunch.form');

        if ($request->request->has($form->getName())                    // submited form
            && $this->getHandler()->handle($form, $request, $task)      // successful handled
            ) {
            return $this->redirectOrDefault('Rhea_homepage');
        }

        return $this->render($template, $this->addTaskParams($task, array(
            'type_dir' => 'Lunch',
            'task'     => $task,
            'form'     => $form->createView()
        )));
    }
}