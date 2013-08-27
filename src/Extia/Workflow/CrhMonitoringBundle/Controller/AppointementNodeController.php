<?php

namespace Extia\Workflow\CrhMonitoringBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * appointement workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class AppointementNodeController extends TypeNodeController
{
    /**
     * {@inherit_doc}
     */
    protected function getTemplates()
    {
        return array(
            'node'             => 'ExtiaWorkflowCrhMonitoringBundle::node.html.twig',
            'modal'            => 'ExtiaWorkflowCrhMonitoringBundle:Appointement:modal.html.twig',
            'notification'     => 'ExtiaWorkflowCrhMonitoringBundle:Appointement:notification.html.twig',
            'timeline_element' => 'ExtiaWorkflowCrhMonitoringBundle:Appointement:timeline_element.html.twig'
        );
    }

    /**
     * use hook method to adds prev task data into new
     *
     * {@inherit_doc}
     */
    protected function onTaskCreation(Request $request, Task $nextTask, Task $prevTask = null, \Pdo $connection = null)
    {
        $nextTask->setUserTargetId($prevTask->getUserTargetId());

        $nextTask->setActivationDate($prevTask->data()->get('notif_date'));
        $nextTask->defineCompletionDate('+1 day');

        $nextTask->data()->set('meeting_date', $prevTask->data()->get('meeting_date'));

        return parent::onTaskCreation($request, $nextTask, $prevTask, $connection);
    }

    /**
     * {@inherit_doc}
     */
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = 'ExtiaWorkflowCrhMonitoringBundle::node.html.twig')
    {
        $error = '';
        $task  = $this->findCurrentTaskByWorkflowId($workflowId, $task);
        $form  = $this->get('crh_monitoring.appointement.form')->setData(array(
            'meeting_date' => $task->data()->get('meeting_date')
        ));

        if ($request->request->has($form->getName())) {

            $handler  = $this->get('crh_monitoring.appointement.handler');
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
            'type_dir' => 'Appointement',
            'form'     => $form->createView()
        ));
    }
}
