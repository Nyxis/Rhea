<?php

namespace Extia\Workflow\AnnualReviewBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;

/**
 * preparing workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class PreparingNodeController extends TypeNodeController
{
    /**
     * {@inherit_doc}
     */
    public function getHandler()
    {
        return $this->get('annual_review.preparing.handler');
    }

    /**
     * {@inherit_doc}
     */
    protected function getTemplates()
    {
        return array(
            'node'             => 'ExtiaWorkflowAnnualReviewBundle::node.html.twig',
            'modal'            => 'ExtiaWorkflowAnnualReviewBundle:Preparing:modal.html.twig',
            'notification'     => 'ExtiaWorkflowAnnualReviewBundle:Preparing:notification.html.twig',
            'timeline_element' => 'ExtiaWorkflowAnnualReviewBundle:Preparing:timeline_element.html.twig'
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

        // re assign to crh if we can
        if ($prevTask->data()->has('crh_id')) {
            $nextTask->setAssignedTo($prevTask->data()->get('crh_id'));
        }

        $meetingDate = $prevTask->data()->get('meeting_date');
        $nextTask->data()->set('meeting_date', $meetingDate);

        $nextTask->setActivationDate($nextTask->findNextWorkingDay(
            (int) $nextTask->calculateDate($meetingDate, '-1 month', 'U')
        ));
        $nextTask->defineCompletionDate('+21 days');

        return parent::onTaskCreation($nextTask, $prevTask, $parameters, $connection);
    }

    /**
     * {@inherit_doc}
     */
    public function onTaskDiffering(Task $task)
    {
        $task->defineCompletionDate('+21 days');
    }

    /**
     * {@inherit_doc}
     */
    protected function executeNode(Request $request, Task $task, $template)
    {
        $data = array(   // default values
            'manager_id'   => $task->getTarget('consultant')->getManager()->getId(),
            'meeting_date' => $task->data()->get('meeting_date')
        );

        $options = array(  // form options
            'document_directory'  => $task->getTarget('consultant')->getUrl(),
            'document_name_model' => $this->get('translator')->trans(
                'annual_review_preparing.document.name', array(), 'messages', $this->container->getParameter('locale')
            )
        );

        $form = $this->get('form.factory')->create('annual_review_preparing_form', $data, $options);

        if ($request->request->has($form->getName())                    // submited form
            && $this->getHandler()->handle($form, $request, $task)      // successful handled
            ) {
            return $this->redirectOrDefault('Rhea_homepage');
        }

        return $this->render($template, $this->addTaskParams($task, array(
            'type_dir' => 'Preparing',
            'task'     => $task,
            'form'     => $form->createView()
        )));
    }
}
