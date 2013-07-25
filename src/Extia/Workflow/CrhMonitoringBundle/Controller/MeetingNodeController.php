<?php

namespace Extia\Workflow\CrhMonitoringBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * meeting workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class MeetingNodeController extends TypeNodeController
{
    /**
     * use hook method to adds prev task data into new
     *
     * {@inherit_doc}
     */
    protected function onTaskCreation(Request $request, Task $nextTask, Task $prevTask = null, \Pdo $connection = null)
    {
        $nextTask->setUserTargetId($prevTask->getUserTargetId());
        $nextTask->setActivationDate($prevTask->data()->get('meeting_date'));

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
        return $this->executeNode($request, $workflowId, $task);
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
        return $this->executeNode($request, $workflowId, $task, 'ExtiaWorkflowCrhMonitoringBundle:Meeting:modal.html.twig');
    }

    /**
     * notification action - renders state of this node for notification
     *
     * @param  Request  $request
     * @param  int      $taskId
     * @return Response
     */
    public function notificationAction(Request $request, $taskId)
    {
        return $this->render('ExtiaWorkflowCrhMonitoringBundle:Meeting:notification.html.twig', array(
            'task' => $this->findTask($taskId)
        ));
    }

    /**
     * timeline action - renders state of this node as timeline
     *
     * @param  Request  $request
     * @param  int      $taskId
     * @return Response
     */
    public function timelineAction(Request $request, $taskId, $params = array())
    {
        return $this->render('ExtiaWorkflowCrhMonitoringBundle:Meeting:timeline_element.html.twig',
            array_replace_recursive($params, array('task' => $this->findTask($taskId)))
        );
    }

    /**
     * execute current node
     *
     * @param  Request  $request    [description]
     * @param  int      $workflowId [description]
     * @param  Task     $task       [description]
     * @param  string   $template   [description]
     * @return Response
     */
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = 'ExtiaWorkflowCrhMonitoringBundle::node.html.twig')
    {
        $error = '';
        $task  = $this->findCurrentTaskByWorkflowId($workflowId, $task);
        $form  = $this->get('crh_monitoring.meeting.form');

        if ($request->request->has($form->getName())) {

            $handler  = $this->get('crh_monitoring.meeting.handler');
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
            'type_dir' => 'Meeting',
            'form'     => $form->createView()
        ));
    }
}
