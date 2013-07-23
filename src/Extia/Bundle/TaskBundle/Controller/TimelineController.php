<?php

namespace Extia\Bundle\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * controller for timelines
 */
class TimelineController extends Controller
{
    /**
     * displays a timeline for given tasks
     *
     * @param  Request  $request
     * @return Response
     */
    public function tasksTimelineAction(Request $request)
    {
        $taskCollection = $request->attributes->get('task_collection');

        // extracts dates from timeline
        $tasksByDate = array();
        foreach ($taskCollection as $task) {
            $node  = $task->getNode();
            $year  = $node->isCurrent() ? $task->getActivationDate('Y') : $node->getCompletedAt('Y');
            $month = $node->isCurrent() ? $task->getActivationDate('n') : $node->getCompletedAt('n');

            if (empty($tasksByDate[$year])) {
                $tasksByDate[$year] = array();
            }
            if (empty($tasksByDate[$year][$month])) {
                $tasksByDate[$year][$month] = array();
            }

            $tasksByDate[$year][$month][] = $task;
        }

        return $this->render('ExtiaTaskBundle:Timeline:tasks_timeline.html.twig', array(
            'tasks'            => $tasksByDate,
            'element_template' => $request->attributes->get('element_template', 'default_timeline_element.html.twig')
        ));
    }
}
