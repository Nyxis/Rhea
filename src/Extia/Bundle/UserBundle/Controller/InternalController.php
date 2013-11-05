<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use Extia\Bundle\MissionBundle\Model\MissionQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for internal actions and features
 */
class InternalController extends Controller
{
    /**
     * displays given internal timeline
     *
     * @param  Request                                                           $request
     * @param  int                                                               $Id      internal id
     * @param  string                                                            $Url     internal url (slug)
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Response
     */
    public function timelineAction(Request $request, $Id, $Url)
    {
        $isAdminGranted = $this->get('security.context')->isGranted('ROLE_ADMIN', $this->getUser());

        // can access this timeline ?
        if (!$isAdminGranted && !$this->get('security.context')->isGranted('ROLE_INTERNAL_READ', $this->getUser())) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access internal timeline.'));
        }

        $locale = $request->attributes->get('_locale');

        $user = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterById($Id)
            ->filterByUrl($Url)
            ->findOne();

        if (empty($user)) {
            throw new NotFoundHttpException(sprintf('Requested internal not found : %s - id %s',
                $Url, $Id
            ));
        }

        // can access this timeline ?
        if (!$isAdminGranted && !$this->get('security.context')->isGranted('USER_DATA', $user)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access %s %s timeline.',
                $user->getFirstname(), $user->getLastname()
            ));
        }

        return $this->render('ExtiaUserBundle:Internal:timeline.html.twig', array(
            'internal' => $user
        ));
    }

    /**
     * list consultants for given internal
     *
     * @param  Request  $request
     * @param  Internal $internal
     * @return Response
     */
    public function consultantsListAction(Request $request, Internal $internal)
    {
        $consultants = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByInternalReferer($internal)
            ->find();

        return $this->render('ExtiaUserBundle:Internal:consultants.html.twig', array(
            'consultants' => $consultants
        ));
    }

    /**
     * render all intercontract consultants for given itnernal
     *
     * @param Request  $request
     * @param Internal $internal
     *
     * @return Response
     */
    public function intercontractsAction(Request $request, Internal $internal)
    {
        $consultantCollection = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))

            ->useMissionOrderQuery()
                ->filterByCurrent(true)
                ->useMissionQuery()
                    ->filterByType('ic')
                ->endUse()
            ->endUse()

            ->filterByActive()
            ->filterByInternalReferer($internal)

            ->find();

        return $this->render('ExtiaUserBundle:Internal:internals.html.twig', array(
            'internal'    => $internal,
            'consultants' => $consultantCollection
        ));
    }

    /**
     * list missions for given internal
     *
     * @param  Request  $request
     * @param  Internal $internal
     * @return Response
     */
    public function missionsListAction(Request $request, Internal $internal)
    {
        $missions = MissionQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByManagerId($internal->getId())
            ->filterByType('client')

            ->joinWith('Client')

            ->joinWith('MissionOrder')
            ->withColumn('COUNT(MissionOrder.Id)', 'NbClt')

            ->groupBy('Id')
            ->orderBy('NbClt', \Criteria::DESC)
            ->find();

        return $this->render('ExtiaUserBundle:Internal:missions.html.twig', array(
            'missions' => $missions
        ));
    }

    /**
     * list past tasks for given user team
     *
     * @param Request  $request
     * @param Internal $internal
     *
     * @return Response
     */
    public function teamPastTasksAction(Request $request, Internal $internal)
    {
        $taskCollection = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            // ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithCurrentNodes()

            ->filterByAssignedTo($internal->getTeamIds()->getData())
            ->filterByWorkflowTypes(array_keys($this->get('workflows')->getAllowed('read')))

            ->filterByCompletionDate(array('max' => 'now'))

            ->orderByActivationDate()

            ->limit(10)

            ->findWithTargets();

        return $this->render('ExtiaUserBundle:Internal:team_past_tasks.html.twig', array (
            'tasks' => $taskCollection
        ));
    }
}
