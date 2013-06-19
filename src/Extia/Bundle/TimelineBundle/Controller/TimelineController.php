<?php

namespace Extia\Bundle\TimelineBundle\Controller;

use Extia\Bundle\UserBundle\Model\User\InternalQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * controller for timeline screens
 */
class TimelineController extends Controller
{
    /**
     * prints user timeline
     * @param  Request  $request
     * @return Response
     */
    public function userTimelineAction(Request $request)
    {
        if ($request->attributes->has('user_id')) {
            $user = InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterById($request->get('user_id'))
                ->filterByFirstname($request->get('firstname'))
                ->filterByLastname($request->get('lastname'))
                ->findOne();

            if (empty($user)) {
                throw new NotFoundHttpException(sprintf('Requested user not found : %s %s (id %s)',
                    $request->get('firstname'),
                    $request->get('lastname'),
                    $request->get('user_id')
                ));
            }
        } else {
            $user = $this->getUser();
        }

        // can access this timeline ?
        if (!$this->get('security.context')->isGranted('USER', $user)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access %s %s timeline.',
                $request->get('firstname'),
                $request->get('lastname')
            ));
        }

        return $this->render('ExtiaTimelineBundle:Timeline:user_timeline.html.twig', array(
            'user' => $user
        ));
    }
}
