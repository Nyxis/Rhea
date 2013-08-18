<?php

namespace Extia\Workflow\AnnualReviewBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * preparing workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class PreparingNodeController extends TypeNodeController
{
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
    protected function onTaskCreation(Request $request, Task $nextTask, Task $prevTask = null, \Pdo $connection = null)
    {
        $nextTask->setUserTargetId($prevTask->getUserTargetId());

        // re assign to crh if we can
        if ($prevTask->data()->has('crh_id')) {
            $nextTask->setAssignedTo($prevTask->data()->get('crh_id'));
        }

        $meetingDate = $prevTask->data()->get('meeting_date');
        $nextTask->data()->set('meeting_date', $meetingDate);

        $nextTask->setActivationDate($nextTask->findNextWorkingDay(
            (int) $nextTask->calculateDate($meetingDate, '-1 month', 'U')
        ));
        $nextTask->defineCompletionDate('+21 day');

        return parent::onTaskCreation($request, $nextTask, $prevTask, $connection);
    }

    /**
     * {@inherit_doc}
     */
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = '')
    {
        $error = '';
        $task  = $this->findCurrentTaskByWorkflowId($workflowId, $task);

        $data = array(   // default values
            'manager_id'   => $task->getUserTarget()->getConsultant()->getManager()->getId(),
            'meeting_date' => $task->data()->get('meeting_date')
        );

        $options = array(  // form options
            'document_directory'  => $task->getUserTarget()->getUrl(),
            'document_name_model' => $this->get('translator')->trans(
                'annual_review_preparing.document.name', array(), 'messages', $this->container->getParameter('locale')
            )
        );

        $form = $this->get('form.factory')->create('annual_review_preparing_form', $data, $options);

        if ($request->request->has($form->getName())) {

            $handler  = $this->get('annual_review.preparing.handler');
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
            'type_dir' => 'Preparing',
            'form'     => $form->createView()
        ));
    }
}
