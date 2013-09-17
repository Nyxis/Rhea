<?php

namespace Extia\Workflow\MissionMonitoringBundle\Controller;

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
     * {@inherit_doc}
     */
    public function getHandler()
    {
        return $this->get('mission_monitoring.meeting.handler');
    }

    /**
     * {@inherit_doc}
     */
    protected function getTemplates()
    {
        return array(
            'node'             => 'ExtiaWorkflowMissionMonitoringBundle::node.html.twig',
            'modal'            => 'ExtiaWorkflowMissionMonitoringBundle:Meeting:modal.html.twig',
            'notification'     => 'ExtiaWorkflowMissionMonitoringBundle:Meeting:notification.html.twig',
            'timeline_element' => 'ExtiaWorkflowMissionMonitoringBundle:Meeting:timeline_element.html.twig'
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

        $nextTask->setActivationDate(strtotime(date('Y-m-d', $prevTask->data()->get('meeting_date'))));
        $nextTask->defineCompletionDate('+2 day');

        $nextTask->data()->set('meeting_date', $prevTask->data()->get('meeting_date'));
        $nextTask->data()->set('contact_name', $prevTask->data()->get('contact_name'));
        $nextTask->data()->set('contact_email', $prevTask->data()->get('contact_email'));
        $nextTask->data()->set('contact_tel', $prevTask->data()->get('contact_tel'));

        return parent::onTaskCreation($request, $nextTask, $prevTask, $connection);
    }

    /**
     * {@inherit_doc}
     */
    public function onTaskDiffering(Task $task)
    {
        $task->defineCompletionDate('+2 days');

        // recalculate meeting date
        $oldDate = $task->data()->get('meeting_date');
        $newDate = $task->getActivationDate();

        $task->data()->set('meeting_date', mktime(
            date('H', $oldDate), date('i', $oldDate), 0,
            $newDate->format('n'), $newDate->format('j'), $newDate->format('Y')
        ));
    }

    /**
     * {@inherit_doc}
     */
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = 'ExtiaWorkflowMissionMonitoringBundle::node.html.twig')
    {
        $error = '';
        $task  = $this->findCurrentTaskByWorkflowId($workflowId, $task);
        $form  = $this->get('form.factory')->create('mission_meeting_form', array(), array(
            'document_directory'  => $task->getUserTarget()->getUrl(),
            'document_name_model' => $this->get('translator')->trans(
                'mission_monitoring.meeting.document.name', array(), 'messages', $this->container->getParameter('locale')
            )
        ));

        if ($request->request->has($form->getName())) {

            $response = $this->getHandler()->handle($form, $request, $task);

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
