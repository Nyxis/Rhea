<?php

namespace Extia\Bundle\DashboardBundle\Controller;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\TaskQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
            ->joinWithCurrentNodes()
            ->find();

        return $this->render('ExtiaDashboardBundle:Task:today_tasks.html.twig', array(
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
            ->joinWithCurrentNodes()
            ->orderByActivationDate()
            ->limit($limit)
            ->find();

        return $this->render('ExtiaDashboardBundle:Task:next_tasks.html.twig', array(
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
            ->joinWithCurrentNodes()
            ->orderByActivationDate()
            ->find();

        return $this->render('ExtiaDashboardBundle:Task:past_tasks.html.twig', array(
            'tasks' => $pastTaskCollection
        ));
    }
}
