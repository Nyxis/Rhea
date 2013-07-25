<?php

namespace Extia\Bundle\TaskBundle\Controller;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use EasyTask\Bundle\WorkflowBundle\Model\WorkflowQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * controller for tasks components
 */
class TaskController extends Controller
{
    /**
     * pre calculated dates
     * @var array
     */
    protected $dates;

    /**
     * __construct
     */
    public function __construct()
    {
        $this->dates = array(
            'today'    => strtotime(date('Y-m-d')),
            'tomorrow' => strtotime(date('Y-m-d')) + 24*3600,
        );
    }

    /**
     * action for workflow details, displays a timeline for
     * given workflow id
     *
     * @param  Request  $request
     * @return Response
     */
    public function workflowHistoryAction(Request $request)
    {
        // find instead of findPk to use join with, and perform always one request
        $tasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))

            ->useNodeQuery()
                ->useWorkflowQuery()
                    ->filterById($request->attributes->get('workflow_id'))
                ->endUse()
                ->orderByCurrent(\Criteria::DESC)
                ->orderByCompletedAt(\Criteria::DESC)
            ->endUse()

            ->joinWithAll()

            ->find();

        if ($tasks->isEmpty()) {
            throw new NotFoundHttpException(sprintf('Any tasks found for given workflow id : "%s"',
                $request->attributes->get('workflow_id')
            ));
        }

        $workflow = $tasks->getFirst()->getNode()->getWorkflow();
        $form     = $this->get('form.factory')->create('workflow_data', $workflow);

        return $this->render('ExtiaTaskBundle:Task:workflow_detail.html.twig', array(
            'workflow' => $workflow,
            'tasks'    => $tasks,
            'form'     => $form->createView()
        ));
    }

    /**
     * edits given workflow with incomming posted form
     * @param  Request  $request
     * @param  int      $workflowId
     * @return Response
     */
    public function workflowEditAction(Request $request, $workflow_id)
    {
        $workflow = WorkflowQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->findPk($workflow_id);

        if (empty($workflow)) {
            throw new \NotFoundHttpException(sprintf('Given workflow id is unknown : "%s" given', $workflow_id));
        }

        $form = $this->get('form.factory')->create('workflow_data', $workflow);
        $form->bind($request);
        if ($form->isValid()) {
            $workflow->save();
        } else {
            $this->get('session')->getFlashbag()->add('error', array(
                'message' => 'workflow.notification.edit_form_invalid'
            ));
        }

        return $this->redirect($request->get('redirect_url',
            $this->get('router')->generate('Rhea_homepage')
        ));
    }

    /**
     * list today tasks for given user id
     * @param  int      $userId
     * @return Response
     */
    public function todayTasksAction($userId)
    {
        $todayTaskCollection = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByAssignedTo($userId)
            ->filterByActivationDate(array(
                'min' => $this->dates['today'],
                'max' => $this->dates['tomorrow'],
            ))
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithTargettedUser()
            ->joinWithCurrentNodes()
            ->find();

        return $this->render('ExtiaTaskBundle:Task:Boxes/today_tasks.html.twig', array(
            'tasks' => $todayTaskCollection
        ));
    }

    /**
     * list next tasks for given user id
     * @param  int      $userId
     * @param  int      $limit
     * @return Response
     */
    public function nextTasksAction($userId, $limit = 10)
    {
        $nextTaskCollection = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByAssignedTo($userId)
            ->filterByActivationDate(array(
                'min' => $this->dates['tomorrow']
            ))
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithTargettedUser()
            ->joinWithCurrentNodes()
            ->orderByActivationDate()
            ->find();

        return $this->render('ExtiaTaskBundle:Task:Boxes/next_tasks.html.twig', array(
            'tasks' => $nextTaskCollection
        ));
    }

    /**
     * list past tasks actions for given user if
     * @param  int      $userId
     * @return Response
     */
    public function pastTasksAction($userId)
    {
        $pastTaskCollection = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByAssignedTo($userId)
            ->filterByActivationDate(array(
                'max' => $this->dates['today']
            ))
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithTargettedUser()
            ->joinWithCurrentNodes()
            ->orderByActivationDate()
            ->find();

        return $this->render('ExtiaTaskBundle:Task:Boxes/past_tasks.html.twig', array(
            'tasks' => $pastTaskCollection
        ));
    }
}
