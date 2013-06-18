<?php

namespace EasyTask\Bundle\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;

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
    public function indexAction(Request $request)
    {
        return $this->render('EasyTaskWorkflowBundle:Timeline:timeline.html.twig', array(
            'current_route' => $request->attributes->get('_route'),
        ));
    }
}
