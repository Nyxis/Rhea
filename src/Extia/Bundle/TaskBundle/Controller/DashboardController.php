<?php

namespace Extia\Bundle\TaskBundle\Controller;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Model\TaskQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * controller for dashboard screens
 */
class DashboardController extends Controller
{
    /**
     * selects and returns all user tasks for dashboard
     * @param  int   $userId
     * @return array
     */
    protected function getUserDashboardTasks($userId, $page = 1)
    {
        if (empty($page) || $page < 1) {
            throw new NotFoundHttpException('Given page is invalid');
        }

        $tasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            // ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithCurrentNodes()

            ->filterByAssignedTo($userId)
            ->filterByWorkflowTypes(array_keys($this->get('workflows')->getAllowed('write')))

            ->orderByActivationDate()
            ->limit(5)
            ->findWithTargets()
            ->getData();

        $page--;
        $maxPerPage = 5;
        $paged = array_slice($tasks, $page*$maxPerPage, $page*$maxPerPage + $maxPerPage);

        return $paged;
    }

    /**
     * prints user timeline
     * @param  Request  $request
     * @param  integer  $page
     * @return Response
     */
    public function userDashboardAction(Request $request, $page = 1)
    {
        if ($this->getUser()->getPersonTypeId() == 2)
        {
            return $this->render('ExtiaTaskBundle:Dashboard:da_dashboard.html.twig', array(
                'user'  => $this->getUser()
             ));
        }
        elseif($this->getUser()->getPersonTypeId() == 1)
        {
            return $this->render('ExtiaTaskBundle:Dashboard:pdg_dashboard.html.twig', array(
                'user'  => $this->getUser()
            ));
        }
        else
        {
            return $this->render('ExtiaTaskBundle:Dashboard:user_dashboard.html.twig', array(
                'user'  => $this->getUser()
            ));
        }
    }

    /**
     * renders a dashboard with given tasks
     *
     * @param  Request  $request
     * @return Response
     */
    public function dashboardTimelineAction(Request $request, $tasks = array(), $userId = null)
    {
        if (empty($tasks)) {
            $tasks = $this->getUserDashboardTasks(
                empty($userId) ? $this->getUser()->getId() : $userId
            );
        }

        // split into differents arrays for time separation
        // (past, today, tomorrow, next week and so on...)
        $temporalizedTasks = array();

        foreach ($tasks as $task) {
            $key = $this->getTemporalKey($task);
            $key = is_numeric($key) ? $key : 'dashboard.timeline.header.'.$key;
            if (empty($temporalizedTasks[$key])) {
                $temporalizedTasks[$key] = array();
            }
            $temporalizedTasks[$key][] = $task;
        }

        return $this->render('ExtiaTaskBundle:Dashboard:dashboardTimeline.html.twig', array(
            'temporalized_tasks' => $temporalizedTasks
        ));
    }

    /**
     * calculates and returns temporal key for given date
     * @param  Task   $task
     * @return string
     */
    protected function getTemporalKey(Task $task)
    {
        $activationDate = $task->getActivationDate();
        if (empty($activationDate)) {
            return 'waiting';
        }

        $temporalTool = $this->get('extia_task.tools.temporal');

        $activationTmsp = intval($activationDate->format('U'));
        $completionTmsp = intval($task->getCompletionDate('U'));
        $today          = strtotime(date('Y-m-d'));

        if ($completionTmsp <= $today) {
            return 'past';
        }

        $tomorrow = $temporalTool->changeDate($today, '+1 day', 'U');
        if ($activationTmsp >= $today && $activationTmsp < $tomorrow) {
            return 'today';
        }

        if ($activationTmsp < $today && $today < $completionTmsp) {
            return 'waiting';
        }

        $nextInWeek = $temporalTool->changeDate($today, '+2 days', 'U');
        if ($activationTmsp >= $tomorrow && $activationTmsp < $nextInWeek) {
            return 'tomorrow';
        }

        $nextWeek = $nextInWeek; // search for next monday
        while (date('N', $nextWeek) != 1) {
            $nextWeek += 3600*24;
        }
        if ($activationTmsp >= $nextInWeek && $activationTmsp < $nextWeek) {
            return 'week';
        }

        if ($activationTmsp >= $nextWeek && $activationTmsp < $temporalTool->changeDate($nextWeek, '+7 days', 'U')) {
            return 'next_week';
        }

        // search for next month
        $thisMonth = strtotime(date('Y-m-1'));
        $nextMonth = $temporalTool->changeDate($thisMonth, '+1 month', 'U');

        if ($activationDate >= $nextMonth && $activationTmsp < $temporalTool->changeDate($nextMonth, '+1 month', 'U')) {
            return 'next_month';
        }

        return $activationDate->format('Y');
    }
}
