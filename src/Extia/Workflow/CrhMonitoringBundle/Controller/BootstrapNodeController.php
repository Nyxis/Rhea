<?php

namespace Extia\Workflow\CrhMonitoringBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * bootstrap workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class BootstrapNodeController extends TypeNodeController
{
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
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = 'ExtiaWorkflowCrhMonitoringBundle::node.html.twig')
    {
        $error = '';
        $task  = $this->findCurrentTaskByWorkflowId($workflowId, $task);
        $form  = $this->get('crh_monitoring.bootstrap.form');

        if ($request->request->has($form->getName())) {

            $handler  = $this->get('crh_monitoring.bootstrap.handler');
            $response = $handler->handle($form, $request, $task);

            // we dont use default redirect response : our tasks are asynchronous
            // we redirect previous page with a message instead
            if (!empty($response)) {
                return $this->redirectWithNodeNotification('success', $task, 'Rhea_homepage');
            }

            $error = $handler->error;
        }

        return $this->render($template, array(
            'error'    => $error,
            'task'     => $task,
            'type_dir' => 'Bootstrap',
            'form'     => $form->createView()
        ));
    }
}
