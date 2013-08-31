<?php

namespace Extia\Bundle\MissionBundle\Controller;

use Extia\Bundle\MissionBundle\Model\MissionQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Form\Form;

/**
 * controller for missions admin
 */
class AdminMissionController extends Controller
{


    /**
     * process sorts from request and session to query, and return all available sorts
     *
     * @param  Request      $request
     * @param  MissionQuery $query
     * @return array
     */
    protected function processSorts(Request $request, MissionQuery $query)
    {
        $session = $this->get('session');

        $sorts = array(
            'client_name' => 'ClientName',
            'label'       => 'Label',
            'manager'     => 'Manager',
            'nb_clt'      => 'NbConsultants',
            'contact'     => 'Contact'
        );

        $defaultSortField     = 'label';
        $defaultSortDirection = 'asc';

        // reset button
        if ($request->request->has('reset_filters')) {
            $session->remove('missions_list_sort_field');
            $session->remove('missions_list_sort_direction');
        }

        $currentSortField     = $request->query->get('sort', $session->get('missions_list_sort_field', $defaultSortField));
        $currentSortDirection = $request->query->get('dir', $session->get('missions_list_sort_direction', $defaultSortDirection));

        if (empty($sorts[$currentSortField]) || !in_array($currentSortDirection, array('asc', 'desc'))) {
            throw new NotFoundHttpException(sprintf('Invalid sort parameters : %s - %s',
                $currentSortField, $currentSortDirection
            ));
        }

        $sortMethod = sprintf('orderBy%s', ucfirst($sorts[$currentSortField]));

        $query->$sortMethod($currentSortDirection);

        $session->set('missions_list_sort_field', $currentSortField);
        $session->set('missions_list_sort_direction', $currentSortDirection);

        return array(
            'field'     => $currentSortField,
            'direction' => $currentSortDirection
        );
    }

    /**
     * calculate current page
     * @param  Request $request
     * @return int
     */
    protected function getCurrentPage(Request $request)
    {
        $session = $this->get('session');

        // reset button
        if ($request->request->has('reset_filters')) {
            $session->set('missions_list_page', null);
        }

        $page = $request->query->get('page',
            $session->get('missions_list_page')
        );

        $page = $page < 1 ? 1 : $page;

        $session->set('missions_list_page', $page);

        return $page;
    }

    /**
     * list missions action
     * @param  Request  $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $user = $this->getUser();
        if (!$this->get('security.context')->isGranted('ROLE_MISSION_READ', $user)) {
            throw new AccessDeniedHttpException(sprintf('Unable to see missions.'));
        }

        $missionsQuery = MissionQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->join('Manager')
            ->join('Client')
            ->join('MissionOrder')

            ->withColumn('COUNT(MissionOrder.Id)', 'NbConsultants')
            ->filterByType('client')

            ->useMissionOrderQuery()
                ->filterByCurrent(true)
            ->endUse()

            ->groupBy('Id')
        ;

        $sorts = $this->processSorts($request, $missionsQuery);

        $missionsCollection = $this->get('knp_paginator')->paginate(
            $missionsQuery, $this->getCurrentPage($request), 10
        );

        return $this->render('ExtiaMissionBundle:AdminMission:list.html.twig', array(
            'missions' => $missionsCollection,
            'sort'     => $sorts
        ));
    }
}