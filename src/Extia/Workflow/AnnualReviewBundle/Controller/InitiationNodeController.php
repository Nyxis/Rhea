<?php

namespace Extia\Workflow\AnnualReviewBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * initiation workflow node controller
 * @see Extia\Bundle\TaskBundle\Workflow\TypeNodeController
 */
class InitiationNodeController extends TypeNodeController
{
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
     * modal action - execution of current node and renderer as a modal
     *
     * @param  Request  $request
     * @param  int      $workflowId
     * @param  Task     $task
     * @return Response
     */
    public function modalAction(Request $request, $workflowId = null, Task $task = null)
    {
        return $this->executeNode($request, $workflowId, $task, 'ExtiaWorkflowAnnualReviewBundle:Initiation:modal.html.twig');
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
        return $this->render('ExtiaWorkflowAnnualReviewBundle:Initiation:notification.html.twig', array(
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
        return $this->render('ExtiaWorkflowAnnualReviewBundle:Initiation:timeline_element.html.twig',
            array_replace_recursive($params, array('task' => $this->findTask($taskId)))
        );
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
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = 'ExtiaWorkflowAnnualReviewBundle::node.html.twig')
    {
        $error = '';
        $task  = $this->findCurrentTaskByWorkflowId($workflowId, $task);
        $form  = $this->get('annual_review.initiation.form');

        if ($request->request->has($form->getName())) {

            $handler  = $this->get('annual_review.initiation.handler');
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
            'type_dir' => 'Initiation',
            'form'     => $form->createView()
        ));
    }
}
