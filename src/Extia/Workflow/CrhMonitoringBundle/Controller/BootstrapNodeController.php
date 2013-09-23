<?php

namespace Extia\Workflow\CrhMonitoringBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;

/**
 * bootstrap workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class BootstrapNodeController extends TypeNodeController
{
    /**
     * {@inherit_doc}
     */
    public function getHandler()
    {
        return $this->get('crh_monitoring.bootstrap.handler');
    }

    /**
     * {@inherit_doc}
     */
    protected function getTemplates()
    {
        return array(
            'node'             => 'ExtiaWorkflowCrhMonitoringBundle::node.html.twig',
            'modal'            => 'ExtiaWorkflowCrhMonitoringBundle:Bootstrap:modal.html.twig',
            'notification'     => 'ExtiaWorkflowCrhMonitoringBundle:Bootstrap:notification.html.twig',
            'timeline_element' => 'ExtiaWorkflowCrhMonitoringBundle:Bootstrap:timeline_element.html.twig'
        );
    }

    /**
     * {@inherit_doc}
     */
    protected function executeNode(Request $request, Task $task, $template)
    {
        $form = $this->get('annual_review.initiation.form');

        if ($request->request->has($form->getName())                    // submited form
            && $this->getHandler()->handle($form, $request, $task)      // successful handled
            ) {
            return $this->redirectOrDefault('Rhea_homepage');
        }

        return $this->render($template, $this->addTaskParams($task, array(
            'type_dir' => 'Bootstrap',
            'task'     => $task,
            'form'     => $form->createView()
        )));
    }
}
