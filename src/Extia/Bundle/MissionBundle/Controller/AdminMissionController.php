<?php

namespace Extia\Bundle\MissionBundle\Controller;

use Extia\Bundle\MissionBundle\Model\Mission;
use Extia\Bundle\MissionBundle\Model\MissionQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Form\Form;

/**
 * controller for missions admin
 */
class AdminMissionController extends Controller
{
    /**
     * injects filters and sorts into query
     *
     * @param  Request      $request
     * @param  MissionQuery $query
     * @return Form
     */
    protected function processFilters(Request $request, MissionQuery $query)
    {
        $session = $this->get('session');

        // reset button
        if ($request->request->has('reset_filters')) {
            $session->remove('mission_filters_data');
        }

        $user = $this->getUser();
        $form = $this->get('extia_mission.form.mission_filters');

        $defaultData = array(
            'display' => $this->get('security.context')->isGranted('ROLE_ADMIN') ? 'all' : 'mine',
        );

        $filters = $session->get('mission_filters_data', $defaultData);

        $form->setData($filters);

        // no incomming form
        if ($request->request->has($form->getName()) && !$request->request->has('reset_filters')) {
            $form->submit($request);
            if ($form->isValid()) {
                $filters = $form->getData();
                $session->set('mission_filters_data', $filters);
            } else {
                $this->get('notifier')->add('warning', 'mission.admin.notifications.filters_error');
            }

            // every submitted form : scratch page in session
            $session->set('missions_list_page', null);
        }

        // adds filters to query

        $user = $this->getUser();
        if ($filters['display'] == 'mine' || !$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $teamIds = $user->getTeamIds();
            if (!empty($teamIds)) {
                $teamIds->append($user->getId());
            } else {
                array_push((array) $teamIds, $user->getId());
            }

            $query->filterByManagerId($teamIds);
        }

        if (!empty($filters['client_name'])) {
            $query->useClientQuery()
                    ->filterByTitle('%'.$filters['client_name'].'%', \Criteria::LIKE)
                ->endUse()
            ;
        }

        if (!empty($filters['mission_label'])) {
            $query->filterByLabel('%'.$filters['mission_label'].'%', \Criteria::LIKE);
        }

        if (!empty($filters['manager'])) {
            $query->filterByManagerId($filters['manager']);
        }

        return $form;
    }

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

        $defaultSortField     = 'client_name';
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
            ->join('MissionOrder', \Criteria::LEFT_JOIN)

            ->filterByType('client')

            ->withColumn('COUNT(MissionOrder.Id)', 'NbConsultants')
            ->condition('current_mission', 'MissionOrder.Current = ?', true)
            ->condition('no_order', 'MissionOrder.Id IS NULL')
            ->where(array('current_mission', 'no_order'), 'or')

            ->groupBy('Id')
        ;

        $filtersForm = $this->processFilters($request, $missionsQuery);

        $sorts = $this->processSorts($request, $missionsQuery);

        $missionsCollection = $this->get('knp_paginator')->paginate(
            $missionsQuery, $this->getCurrentPage($request), 10
        );

        return $this->render('ExtiaMissionBundle:AdminMission:list.html.twig', array(
            'missions' => $missionsCollection,
            'form'     => $filtersForm->createView(),
            'sort'     => $sorts
        ));
    }

    /**
     * displays an edit form for given mission
     *
     * @param  Request  $request
     * @param  Mission  $mission
     * @return Response
     */
    public function editAction(Request $request, Mission $mission)
    {
        return $this->renderForm(
            $request,
            $mission,
            'mission_edit_form',
            'ExtiaMissionBundle:AdminMission:edit.html.twig'
        );
    }

    /**
     * displays and handles mission creation form within a modal
     *
     * @param  Request  $request
     * @return Response
     */
    public function modalAction(Request $request, Mission $mission = null)
    {
        if (!$mission instanceof Mission) {
            $mission = new Mission();
        }

        return $this->renderForm(
            $request,
            $mission,
            'mission_new_form',
            'ExtiaMissionBundle:AdminMission:modal.html.twig'
        );
    }

    /**
     * handles mission creation (only supports ajax submit for now)
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function newAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new NotFoundHttpException('Mission creation is only supported on ajax requests for now');
        }

        $mission = new Mission();

        // handles given form
        $this->renderForm($request, $mission, 'mission_new_form');

        $missionCollection = MissionQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->find();

        $this->get('notifier')->add('success', 'mission.admin.notification.save_success', array(
            '%mission_name%' => $mission->getFullLabel()
        ));

        return new JsonResponse(array(
            'notifications' => $this->get('notifier')->all(),
            'current'       => $mission->getId(),
            'missions'      => $missionCollection->toKeyValue('Id', 'FullLabel')
        ));
    }

    /**
     * displays form for given mission
     *
     * @param  Request  $request
     * @param  Mission  $mission
     * @param  sting    $formType
     * @param  string   $template
     * @return Response
     */
    protected function renderForm(Request $request, Mission $mission, $formType, $template = null)
    {
        $user = $this->getUser();
        if (!$this->get('security.context')->isGranted('ROLE_MISSION_WRITE', $user)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to write missions.'));
        }

        $form = $this->get('form.factory')->create($formType, $mission, array());
        if (!$mission->isNew()) {
            $form->get('client')->setData($mission->getClient());
        }

        if ($request->request->has($form->getName())) {
            $success = $this->get('extia_mission.form.mission_handler')->handle($form, $request);
        }

        return empty($template) ? !empty($success) : $this->render($template, array(
            'mission' => $mission,
            'form'    => $form->createView()
        ));
    }
}
