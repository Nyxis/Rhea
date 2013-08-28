<?php

namespace Extia\Workflow\AnnualReviewBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * annual meeting workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class AnnualMeetingNodeController extends TypeNodeController
{
    /**
     * {@inherit_doc}
     */
    public function getHandler()
    {
        return $this->get('annual_review.annual_meeting.handler');
    }

    /**
     * {@inherit_doc}
     */
    protected function getTemplates()
    {
        return array(
            'node'             => 'ExtiaWorkflowAnnualReviewBundle::node.html.twig',
            'modal'            => 'ExtiaWorkflowAnnualReviewBundle:AnnualMeeting:modal.html.twig',
            'notification'     => 'ExtiaWorkflowAnnualReviewBundle:AnnualMeeting:notification.html.twig',
            'timeline_element' => 'ExtiaWorkflowAnnualReviewBundle:AnnualMeeting:timeline_element.html.twig'
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

        $nextTask->data()->set('crh_id', $prevTask->getAssignedTo());
        $nextTask->setAssignedTo($prevTask->data()->get('manager_id'));

        $nextTask->setActivationDate(strtotime(date('Y-m-d', $prevTask->data()->get('meeting_date'))));
        $nextTask->defineCompletionDate('+2 days');

        $nextTask->data()->set('meeting_date', $prevTask->data()->get('meeting_date'));

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
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = '')
    {
        $error = '';
        $task  = $this->findCurrentTaskByWorkflowId($workflowId, $task);

        $options = array(  // form options
            'document_directory'  => $task->getUserTarget()->getUrl(),
            'document_name_model' => $this->get('translator')->trans(
                'annual_review_annual_meeting.document.name', array(), 'messages', $this->container->getParameter('locale')
            )
        );

        $form = $this->get('form.factory')->create('annual_review_annual_meeting_form', array(), $options);

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
            'type_dir' => 'AnnualMeeting',
            'form'     => $form->createView()
        ));
    }
}
