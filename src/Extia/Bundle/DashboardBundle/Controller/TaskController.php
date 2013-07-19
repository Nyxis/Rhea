<?php

namespace Extia\Bundle\DashboardBundle\Controller;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\TaskQuery;
use Extia\Bundle\UserBundle\Model\User\InternalQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
    public function workflowDetailsAction(Request $request)
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

            // joins
            ->joinWith('Node')
            ->joinWith('Node.Workflow')
            ->joinWith('UserTarget')
            ->joinWith('Comment', \Criteria::LEFT_JOIN)

            ->find();

        if ($tasks->isEmpty()) {
            throw new NotFoundHttpException(sprintf('Any tasks found for given workflow id : "%s"',
                $request->attributes->get('workflow_id')
            ));
        }

        return $this->render('ExtiaDashboardBundle:Task:workflow_detail.html.twig', array(
            'workflow' => $tasks->getFirst()->getNode()->getWorkflow(),
            'tasks'    => $tasks
        ));
    }

    /**
     * displays given user all task which target him
     *
     * @param  Request  $request
     * @return Response
     */
    public function userTasksAction(Request $request, $userId, $firstname, $lastname)
    {
        $locale = $request->attributes->get('_locale');

        $user = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterById($userId)
            ->filterByFirstname($firstname)
            ->filterByLastname($lastname)

            ->joinWith('ConsultantRelatedById', \Criteria::LEFT_JOIN)
            ->joinWith('Group')
            ->useGroupQuery()
                ->joinWithI18n($locale)
            ->endUse()
            ->joinWith('Job')
            ->useJobQuery()
                ->joinWithI18n($locale)
            ->endUse()

            ->findOne();

        if (empty($user)) {
            throw new NotFoundHttpException(sprintf('Requested user not found : %s %s (id %s)',
                firstname, lastname, userId
            ));
        }

        // can access this timeline ?
        if (!$this->get('security.context')->isGranted('USER_DATA', $user)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access %s %s timeline.',
                firstname, lastname
            ));
        }

        $tasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->distinct('Task.Id')

            ->filterByUserTargetId($user->getId())
            ->_or()
            ->filterByAssignedTo($user->getId())

            ->useNodeQuery()
                ->orderByCurrent(\Criteria::DESC)
                ->orderByCompletedAt(\Criteria::DESC)
            ->endUse()

            // joins
            ->joinWith('Node')
            ->joinWith('Node.Workflow')
            ->joinWith('UserTarget')
            ->joinWith('Comment', \Criteria::LEFT_JOIN)

            ->find();

        $template = 'ExtiaDashboardBundle:Task:Users/'.$user->getGroup()->getCode().'_tasks.html.twig';
        if (!$this->get('templating')->exists($template)) {
            $template = 'ExtiaDashboardBundle:Task:Users/user_tasks.html.twig';
        }

        return $this->render($template, array(
            'user'  => $user->getConsultantRelatedById(),
            'tasks' => $tasks
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
            ->joinWith('UserTarget')
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithCurrentNodes()
            ->find();

        return $this->render('ExtiaDashboardBundle:Task:Boxes/today_tasks.html.twig', array(
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
            ->joinWith('UserTarget')
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithCurrentNodes()
            ->orderByActivationDate()
            ->find();

        return $this->render('ExtiaDashboardBundle:Task:Boxes/next_tasks.html.twig', array(
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
            ->joinWith('UserTarget')
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithCurrentNodes()
            ->orderByActivationDate()
            ->find();

        return $this->render('ExtiaDashboardBundle:Task:Boxes/past_tasks.html.twig', array(
            'tasks' => $pastTaskCollection
        ));
    }
}
