<?php

namespace Extia\Workflow\MissionMonitoringBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;

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
    protected function onTaskCreation(Task $nextTask, Task $prevTask = null, array $parameters = array(), \Pdo $connection = null)
    {
        $nextTask->setUserTargetId($prevTask->getUserTargetId());

        $nextTask->setActivationDate($prevTask->data()->get('notif_date'));
        $nextTask->defineCompletionDate('+1 day');

        $nextTask->data()->set('meeting_date', $prevTask->data()->get('next_meeting_date'));

        return parent::onTaskCreation($nextTask, $prevTask, $parameters, $connection);
    }

    /**
     * {@inherit_doc}
     */
    protected function executeNode(Request $request, Task $task, $template)
    {
        $clt  = $task->getUserTarget()->getConsultant();

        $form = $this->get('mission_monitoring.appointement.form')->setData(array(
            'meeting_date'  => $task->data()->get('meeting_date'),
            'contact_name'  => $clt->getCurrentMission()->getContactName(),
            'contact_email' => $clt->getCurrentMission()->getContactEmail(),
            'contact_tel'   => $clt->getCurrentMission()->getContactPhone(),
        ));

        if ($request->request->has($form->getName())                    // submited form
            && $this->getHandler()->handle($form, $request, $task)      // successful handled
            ) {
            return $this->redirectOrDefault('Rhea_homepage');
        }

        return $this->render($template, $this->addTaskParams($task, array(
            'type_dir' => 'Appointement',
            'task'     => $task,
            'form'     => $form->createView()
        )));
    }
}
