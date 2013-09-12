<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Form\Form;

/**
 * controller for consultant admin
 */
class AdminConsultantController extends Controller
{
    /**
     * injects filters and sorts into query
     * @return Form
     */
    protected function processFilters(Request $request, ConsultantQuery $query)
    {
        $session = $this->get('session');

        // reset button
        if ($request->request->has('reset_filters')) {
            $session->remove('consultant_filters_data');
        }

        $user = $this->getUser();
        $form = $this->get('extia_user.admin.consultant_filters_form');

        $defaultData = array(
            'display' => 'mine',
            'agency'  => $user->getAgencyId(),
            'status'  => 'active'
        );

        $filters = $session->get('consultant_filters_data', $defaultData);

        $form->setData($filters);

        // incomming form
        if ($request->request->has($form->getName()) && !$request->request->has('reset_filters')) {
            $form->submit($request);
            if ($form->isValid()) {
                $filters = $form->getData();
                $session->set('consultant_filters_data', $filters);
            } else {
                $this->get('notifier')->add('warning', 'consultant.admin.notifications.filters_error');
            }

            // every submitted form : scratch page in session
            $session->set('consultants_list_page', null);
        }

        // display
        if ($filters['display'] == 'mine') {
            $query->filterByInternalReferer($user);
        }
        // agency
        if (!empty($filters['agency_id'])) {
            $query->filterByAgencyId($filters['agency_id']);
        }
        // name
        if (!empty($filters['name'])) {
            $query->filterByName($filters['name']);
        }
        // status
        if (!empty($filters['status'])) {
            // active by default
            $query->_if($filters['status'] == 'resigned')
                    ->filterByInactive()
                ->_else()
                    ->filterByActive()
                    ->filterByStatus($filters['status'])
                ->_endif();
        }
        // manager
        if (!empty($filters['manager'])) {
            $query->filterByManagerId($filters['manager']);
        }
        // crh
        if (!empty($filters['crh'])) {
            $query->filterByCrhId($filters['crh']);
        }
        // client
        if (!empty($filters['client'])) {
            $query->filterByClient($filters['client'], true); // current client
        }

        return $form;
    }

    /**
     * process sorts from request and session to query, and return all available sorts
     *
     * @param  Request         $request
     * @param  ConsultantQuery $query
     * @return array
     */
    protected function processSorts(Request $request, ConsultantQuery $query)
    {
        $session = $this->get('session');

        $sorts = array(
            'name'       => 'Lastname',
            'entry_date' => 'ContractBeginDate',
            'mission'    => 'CurrentMission'
        );

        $defaultSortField     = 'name';
        $defaultSortDirection = 'asc';

        // reset button
        if ($request->request->has('reset_filters')) {
            $session->remove('consultants_list_sort_field');
            $session->remove('consultants_list_sort_direction');
        }

        $currentSortField     = $request->query->get('sort', $session->get('consultants_list_sort_field', $defaultSortField));
        $currentSortDirection = $request->query->get('dir', $session->get('consultants_list_sort_direction', $defaultSortDirection));

        if (empty($sorts[$currentSortField]) || !in_array($currentSortDirection, array('asc', 'desc'))) {
            throw new NotFoundHttpException(sprintf('Invalid sort parameters : %s - %s',
                $currentSortField, $currentSortDirection
            ));
        }

        $sortMethod = sprintf('orderBy%s', ucfirst($sorts[$currentSortField]));

        $query->$sortMethod($currentSortDirection);

        $session->set('consultants_list_sort_field', $currentSortField);
        $session->set('consultants_list_sort_direction', $currentSortDirection);

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
            $session->get('consultants_list_page')
        );

        $page = $page < 1 ? 1 : $page;

        $session->set('consultants_list_page', $page);

        return $page;
    }

    /**
     * lists all user consultants
     *
     * @param  Request  $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $internal = $this->getUser();
        if (!$this->get('security.context')->isGranted('ROLE_CONSULTANT_READ', $internal)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access consultants.'));
        }

        $consultantsQuery = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__));

        $filtersForm = $this->processFilters($request, $consultantsQuery);

        $sorts = $this->processSorts($request, $consultantsQuery);

        $consultantCollection = $this->get('knp_paginator')->paginate(
            $consultantsQuery, $this->getCurrentPage($request), 10
        );

        return $this->render('ExtiaUserBundle:AdminConsultant:list.html.twig', array(
            'user'        => $internal,
            'consultants' => $consultantCollection,
            'form'        => $filtersForm->createView(),
            'sort'        => $sorts
        ));
    }

    /**
     * renders a new consultant form
     *
     * @param  Request  $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $consultant = new Consultant();

        return $this->renderForm($request, $consultant, 'ExtiaUserBundle:AdminConsultant:new.html.twig');
    }

    /**
     * renders an edit form for given user id
     *
     * @param  Request    $request
     * @param  Consultant $consultant
     * @return Response
     */
    public function editAction(Request $request, Consultant $consultant)
    {
        return $this->renderForm($request, $consultant, 'ExtiaUserBundle:AdminConsultant:edit.html.twig');
    }

    /**
     * executes form on given consultant and renders it on given template
     *
     * @param  Request    $request
     * @param  Consultant $consultant
     * @param  string     $template
     * @return Response
     */
    public function renderForm(Request $request, Consultant $consultant, $template)
    {
        $user = $this->getUser();
        if (!$this->get('security.context')->isGranted('ROLE_CONSULTANT_WRITE', $user)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to write internals.'));
        }

        $isNew = $consultant->isNew();
        $form  = $this->get('form.factory')->create('consultant_form', $consultant, array(
            'consultant_id' => $isNew ? null : $consultant->getId()
        ));

        if ($request->request->has($form->getName())) {
            if ($this->get('extia_user.admin.consultant_form_handler')->handle($form, $request)) {
                // redirect on edit if was new
                if ($isNew) {
                    return $this->redirect($this->get('router')->generate(
                        'UserBundle_consultant_edit', $consultant->getRouting()
                    ));
                }
            }
        }

        return $this->render($template, array(
            'consultant' => $consultant,
            'form'       => $form->createView()
        ));
    }
}
