<?php

namespace Extia\Workflow\MissionMonitoringBundle\Controller;

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
    public function getHandler()
    {
        return $this->get('mission_monitoring.appointement.handler');
    }

    /**
     * {@inherit_doc}
     */
    protected function getTemplates()
    {
        return array(
            'node'             => 'ExtiaWorkflowMissionMonitoringBundle::node.html.twig',
            'modal'            => 'ExtiaWorkflowMissionMonitoringBundle:Appointement:modal.html.twig',
            'notification'     => 'ExtiaWorkflowMissionMonitoringBundle:Appointement:notification.html.twig',
            'timeline_element' => 'ExtiaWorkflowMissionMonitoringBundle:Appointement:timeline_element.html.twig'
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

        $nextTask->data()->set('meeting_date', $prevTask->data()->get('next_meeting_date'));

        return parent::onTaskCreation($request, $nextTask, $prevTask, $connection);
    }

    /**
     * {@inherit_doc}
     */
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = 'ExtiaWorkflowMissionMonitoringBundle::node.html.twig')
    {
        $error = '';
        $task  = $this->findCurrentTaskByWorkflowId($workflowId, $task);
        $clt   = $task->getUserTarget()->getConsultant();

        $form  = $this->get('mission_monitoring.appointement.form')->setData(array(
            'meeting_date'  => $task->data()->get('meeting_date'),
            'contact_name'  => $clt->getCurrentMission()->getContactName(),
            'contact_email' => $clt->getCurrentMission()->getContactEmail(),
            'contact_tel'   => $clt->getCurrentMission()->getContactPhone(),
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
            'type_dir' => 'Appointement',
            'form'     => $form->createView()
        ));
    }
}
