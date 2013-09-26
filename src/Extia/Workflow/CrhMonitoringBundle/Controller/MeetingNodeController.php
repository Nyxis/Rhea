<?php

namespace Extia\Workflow\CrhMonitoringBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;

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
        return $this->get('crh_monitoring.meeting.handler');
    }

    /**
     * {@inherit_doc}
     */
    protected function getTemplates()
    {
        return array(
            'node'             => 'ExtiaWorkflowCrhMonitoringBundle::node.html.twig',
            'modal'            => 'ExtiaWorkflowCrhMonitoringBundle:Meeting:modal.html.twig',
            'notification'     => 'ExtiaWorkflowCrhMonitoringBundle:Meeting:notification.html.twig',
            'timeline_element' => 'ExtiaWorkflowCrhMonitoringBundle:Meeting:timeline_element.html.twig'
        );
    }

    /**
     * use hook method to adds prev task data into new
     *
     * {@inherit_doc}
     */
    protected function onTaskCreation(Task $nextTask, Task $prevTask = null, array $parameters = array(), \Pdo $connection = null)
    {
        $nextTask->migrateTargets($prevTask);

        $nextTask->setActivationDate(strtotime(date('Y-m-d', $prevTask->data()->get('meeting_date'))));
        $nextTask->defineCompletionDate('+2 day');

        $nextTask->data()->set('meeting_date', $prevTask->data()->get('meeting_date'));

        return parent::onTaskCreation($nextTask, $prevTask, $parameters, $connection);
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
    protected function executeNode(Request $request, Task $task, $template)
    {
        $options = array(
            'document_directory'  => $task->getTarget('consultant')->getUrl(),
            'document_name_model' => $this->get('translator')->trans(
                'crh_monitoring.meeting.document.name', array(), 'messages', $this->container->getParameter('locale')
            )
        );

        $form = $this->get('form.factory')->create('meeting_form', array(), $options);

        if ($request->request->has($form->getName())                    // submited form
            && $this->getHandler()->handle($form, $request, $task)      // successful handled
            ) {
            return $this->redirectOrDefault('Rhea_homepage');
        }

        return $this->render($template, $this->addTaskParams($task, array(
            'type_dir' => 'Meeting',
            'task'     => $task,
            'form'     => $form->createView()
        )));
    }
}
