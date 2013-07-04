<?php

namespace Extia\Workflow\CrhMonitoringBundle\Controller;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\Task;
use Extia\Bundle\ExtraWorkflowBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * appointement workflow node controller
 * @see Extia\Bundle\ExtraWorkflowBundle\Workflow\TypeNodeController
 */
class AppointementNodeController extends TypeNodeController
{
    /**
     * use hook method to adds prev task data into new
     *
     * {@inherit_doc}
     */
    protected function onTaskCreation(Request $request, Task $nextTask, Task $prevTask = null, \Pdo $connection = null)
    {
        $nextTask->setUserTargetId($prevTask->getUserTargetId());
        $nextTask->setActivationDate($prevTask->data()->get('notif_date'));
        $nextTask->data()->set('meeting_date', $prevTask->data()->get('meeting_date'));

        return parent::onTaskCreation($request, $nextTask, $prevTask, $connection);
    }

    /**
     * node action - execution of current node
     *
     * @param  Request  $request
     * @param  int      $workflowId
     * @param  Task     $task
     * @return Response
     */
    public function nodeAction(Request $request, $workflowId = null, Task $task = null)
    {
        return $this->executeNode($request, $workflowId, $task, 'ExtiaWorkflowCrhMonitoringBundle:Appointement:node.html.twig');
    }

    /**
     * node action - execution of current node and renderer as a modal
     *
     * @param  Request  $request
     * @param  int      $workflowId
     * @param  Task     $task
     * @return Response
     */
    public function modalAction(Request $request, $workflowId = null, Task $task = null)
    {
        return $this->executeNode($request, $workflowId, $task, 'ExtiaWorkflowCrhMonitoringBundle:Appointement:modal.html.twig');
    }

    /**
     * notification action - renders state of this node for notification
     *
     * @param  Request  $request
     * @param  int      $workflowId
     * @return Response
     */
    public function notificationAction(Request $request, $taskId)
    {
        return $this->render('ExtiaWorkflowCrhMonitoringBundle:Appointement:notification.html.twig', array(
            'task' => $this->findTask($taskId)
        ));
    }

    /**
     * execute current node
     *
     * @param  Request  $request
     * @param  int      $workflowId
     * @param  Task     $task
     * @param  string   $template
     * @return Response
     */
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = 'ExtiaWorkflowCrhMonitoringBundle:Appointement:node.html.twig')
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
            // we redirect to dashboard with a message instead
            if (!empty($response)) {
                return $this->redirectWithNodeNotification('success', $task, 'Rhea_homepage');
            }

            $error = $handler->error;
        }

        return $this->render($template, array(
            'error' => $error,
            'task'  => $task,
            'form'  => $form->createView()
        ));
    }
}
