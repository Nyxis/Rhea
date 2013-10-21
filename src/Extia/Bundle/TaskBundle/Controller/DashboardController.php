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
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithCurrentNodes()

            ->filterByAssignedTo($userId)
            ->filterByWorkflowTypes(array_keys($this->get('workflows')->getAllowed('write')))

            ->orderByActivationDate()
            ->findWithTargets()
            ->getData();

        $page--;
        $maxPerPage = 10;
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
        return $this->render('ExtiaTaskBundle:Dashboard:user_dashboard.html.twig', array(
            'user'  => $this->getUser()
        ));
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

        $activationTmsp = intval($activationDate->format('U'));
        $completionTmsp = intval($task->getCompletionDate('U'));
        $today          = strtotime(date('Y-m-d'));

        if ($completionTmsp <= $today) {
            return 'past';
        }

        $tomorrow = $task->calculateDate($today, '+1 day', 'U');
        if ($activationTmsp >= $today && $activationTmsp < $tomorrow) {
            return 'today';
        }

        if ($activationTmsp < $today && $today < $completionTmsp) {
            return 'waiting';
        }

        $nextInWeek = $task->calculateDate($today, '+2 days', 'U');
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

        if ($activationTmsp >= $nextWeek && $activationTmsp < $task->calculateDate($nextWeek, '+7 days', 'U')) {
            return 'next_week';
        }

        // search for next month
        $thisMonth = strtotime(date('Y-m-1'));
        $nextMonth = $task->calculateDate($thisMonth, '+1 month', 'U');

        if ($activationDate >= $nextMonth && $activationTmsp < $task->calculateDate($nextMonth, '+1 month', 'U')) {
            return 'next_month';
        }

        return $activationDate->format('Y');
    }
}
