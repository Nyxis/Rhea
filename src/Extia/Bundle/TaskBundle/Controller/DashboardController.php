<?php

namespace Extia\Bundle\TaskBundle\Controller;

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
     * prints user timeline
     * @param  Request  $request
     * @return Response
     */
    public function userDashboardAction(Request $request)
    {
        return $this->render('ExtiaTaskBundle:Dashboard:user_dashboard.html.twig', array(
            'user' => $this->getUser()
        ));
    }

    /**
     * renders a partial timeline for current user, use knppaginator for lightweight loading
     *
     * @param  Request  $request
     * @param  integer  $page
     * @return Response
     */
    public function dashboardTimelineAction(Request $request, $page = 1)
    {
        if (empty($page) || $page < 1) {
            throw new NotFoundHttpException('Given page is invalid');
        }

        $tasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithTargettedUser()
            ->joinWithCurrentNodes()

            ->filterByAssignedTo($this->getUser()->getId())
            ->filterByWorkflowTypes(array_keys($this->get('workflows')->getAllowed('write')))

            ->orderByActivationDate()
            ->find()
            ->getData();

        $page--;
        $maxPerPage = 10;
        $paged = array_slice($tasks, $page*$maxPerPage, $page*$maxPerPage + $maxPerPage);

        // split into differents arrays for time separation
        // (past, today, tomorrow, next week and so on...)
        $temporalizedTasks = array();

        foreach ($paged as $task) {
            $activationDate = $task->getActivationDate();
            if (empty($activationDate)) {
                continue; // not initialized tasks have any dates
            }

            $key = $this->getTemporalKey($activationDate);
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
     * @param  \DateTime $date
     * @return string
     */
    protected function getTemporalKey(\DateTime $date)
    {
        $timestamp = intval($date->format('U'));
        $today     = strtotime(date('Y-m-d'));

        if ($timestamp < $today) {
            return 'past';
        }

        $tomorrow = $today + 3600*24;
        if ($timestamp >= $today && $timestamp < $tomorrow) {
            return 'today';
        }

        $nextInWeek = $tomorrow + 3600*24;
        if ($timestamp >= $tomorrow && $timestamp < $nextInWeek) {
            return 'tomorrow';
        }

        $nextWeek = $nextInWeek; // search for next monday
        while (date('N', $nextWeek) != 1) {
            $nextWeek += 3600*24;
        }
        if ($timestamp >= $nextInWeek && $timestamp < $nextWeek) {
            return 'week';
        }

        $after = $nextWeek + 3600*24*7;

        return $timestamp >= $nextWeek && $timestamp < $after ? 'next_week' : 'after';
    }
}
