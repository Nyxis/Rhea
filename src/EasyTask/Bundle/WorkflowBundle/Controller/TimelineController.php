<?php

namespace EasyTask\Bundle\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * timeline controller
 * works with aggregator service to build timeline for given user
 */
class TimelineController extends Controller
{
    /**
     * index action for timeline
     * prints timeline for given username
     */
    public function indexAction($name)
    {
        // retrieve tasks for given username
        $tasksCollection = $this->get('easy_task')
            ->getTasksForUser($name);

        return $this->render($this->container->getParameter('easy_task.timeline_template'), array(
            'layout'        => $this->container->getParameter('easy_task.base_layout'),
            'current_route' => $this->get('request')->attributes->get('_route'),
            'tasks'         => $tasksCollection,
            'name'          => $name
        ));
    }
}
