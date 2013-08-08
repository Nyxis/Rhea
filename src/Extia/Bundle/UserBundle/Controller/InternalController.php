<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\Internal;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for internal actions and features
 */
class InternalController extends Controller
{
    /**
     * list past tasks for given user team
     * @param  Request  $request
     * @param  Internal $internal
     * @return Response
     */
    public function teamPastTasksAction(Request $request, Internal $internal)
    {
        $taskCollection = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithTargettedUser()
            ->joinWithCurrentNodes()

            ->filterByAssignedTo($internal->getTeamIds())
            ->filterByWorkflowTypes(array_keys($this->get('workflows')->getAllowed('read')))

            ->orderByActivationDate()
            ->find();

        return $this->render('ExtiaUserBundle:Internal:team_past_tasks_box.html.twig', array(
            'tasks' => $taskCollection
        ));
    }
}
