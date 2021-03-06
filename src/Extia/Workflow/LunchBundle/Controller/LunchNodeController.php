<?php

namespace Extia\Workflow\LunchBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;
use Extia\Bundle\MissionBundle\Model\MissionQuery;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;

/**
 * bootstrap workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class LunchNodeController extends TypeNodeController
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
     * use hook method to adds prev task data into new
     *
     * {@inherit_doc}
     */
    protected function onTaskCreation(Task $nextTask, Task $prevTask = null, array $parameters = array(), \Pdo $connection = null)
    {
        $lunchTaskDomain = $this->get('lunch.domain.lunch_task');
        $taskTargets = $prevTask->getTaskTargets();

        // We recalculate lunch targets
        $mission = $lunchTaskDomain->getLunchTargetedMission($taskTargets, $connection);
        $nextTask = $lunchTaskDomain->calculateLunchTargets($mission, $nextTask, $connection);

        // activation
        $this->get('extia_task.domain.task')->activateTaskOn(
            $nextTask,
            $prevTask->data()->get('meeting_date'),
            '+1 day'
        );

        $nextTask->data()->set('meeting_date', $prevTask->data()->get('meeting_date'));
        $nextTask->data()->set('meeting_place', $prevTask->data()->get('meeting_place'));

        return parent::onTaskCreation($nextTask, $prevTask, $parameters, $connection);
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
