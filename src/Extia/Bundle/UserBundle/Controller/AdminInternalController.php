<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\InternalQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for internal admin
 */
class AdminInternalController extends Controller
{
    /**
     * injects filters and sorts into query
     * @return Form
     */
    protected function processFilters(Request $request, InternalQuery $query)
    {
        $session = $this->get('session');

        // reset button
        if ($request->request->has('reset_filters')) {
            $session->remove('internal_filters_data');
        }

        $user    = $this->getUser();
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN', $user);
        $form    = $this->get('extia_user.admin.internal_filters_form');

        $defaultData = array(
            'display' => $isAdmin ? 'all' : 'mine',
            'agency'  => $isAdmin ? null : $user->getAgencyId()
        );

        $filters = $session->get('internal_filters_data', $defaultData);

        $form->setData($filters);

        // no incomming form
        if ($request->request->has($form->getName()) && !$request->request->has('reset_filters')) {
            $form->submit($request);
            if ($form->isValid()) {
                $filters = $form->getData();
                $session->set('internal_filters_data', $filters);
            } else {
                $this->get('notifier')->add('warning', 'internal.admin.notifications.filters_error');
            }
        }

        // person type
        if (!empty($filters['internal_type'])) {
            $query->filterByPersonTypeId($filters['internal_type']);
        }
        // agency
        if (!empty($filters['agency'])) {
            $query->filterByAgencyId($filters['agency']);
        }
        // name
        if (!empty($filters['name'])) {
            $query->filterByName($filters['name']);
        }

        // tree filters
        $query->_if(!$isAdmin || $filters['display'] == 'mine')
                ->descendantsOf($user)
            ->_endif();

        // parent
        if (!empty($filters['parent'])) {
            $query->descendantsOf(
                InternalQuery::create()
                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                    ->findPk($filters['parent'])
            );
        }

        // with ic
        if (!empty($filters['with_ic'])) {
            $query->filterByNbIc(0, \Criteria::GREATER_THAN);
        }

        return $form;
    }

    /**
     * process sorts from request and session to query, and return all available sorts
     *
     * @param  Request       $request
     * @param  InternalQuery $query
     * @return array
     */
    protected function processSorts(Request $request, InternalQuery $query)
    {
        $session = $this->get('session');

        $sorts = array(
            'tree'          => 'TreeLeft',
            'name'          => 'Lastname',
            'job'           => 'PersonTypeId',
            'nb_clt'        => 'NbConsultants',
            'nb_ic'         => 'NbIc',
            'nb_past_tasks' => 'NbPastTasks'
        );

        $defaultSortField     = 'tree';
        $defaultSortDirection = 'asc';

        $currentSortField     = $request->query->get('sort', $session->get('internal_list_sort_field', $defaultSortField));
        $currentSortDirection = $request->query->get('dir', $session->get('internal_list_sort_direction', $defaultSortDirection));

        // reset button
        if ($request->request->has('reset_filters')) {
            $session->remove('internal_list_sort_field');
            $session->remove('internal_list_sort_direction');
            $currentSortField     = $defaultSortField;
            $defaultSortDirection = $defaultSortDirection;
        }

        if (empty($sorts[$currentSortField]) || !in_array($currentSortDirection, array('asc', 'desc'))) {
            throw new NotFoundHttpException(sprintf('Invalid sort parameters : %s - %s',
                $currentSortField, $currentSortDirection
            ));
        }

        $sortMethod = sprintf('orderBy%s', ucfirst($sorts[$currentSortField]));

        $query->$sortMethod($currentSortDirection);

        // re inject default as 2nd sort
        if ($currentSortField != $defaultSortField) {
            $query->orderByTreeLeft(\Criteria::ASC);
        }

        $session->set('internal_list_sort_field', $currentSortField);
        $session->set('internal_list_sort_direction', $currentSortDirection);

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
            $session->set('consultants_list_page', null);
        }

        $page = $request->query->get('page',
            $session->get('consultants_list_page')
        );

        $page = $page < 1 ? 1 : $page;

        $session->set('consultants_list_page', $page);

        return $page;
    }

    /**
     * lists internals
     *
     * @param  Request  $request
     * @return Response
     */
    public function listAction(Request $request, $page = 1)
    {
        $internal = $this->getUser();
        if (!$this->get('security.context')->isGranted('ROLE_INTERNAL_READ', $internal)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to internals.',
                $internal->getFirstname(), $internal->getLastname()
            ));
        }

        $internalsQuery = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->innerJoin('Person')
            ->joinWith('Person.PersonType')

            ->selectCountTasks()
            ->selectCountConsultants()

            ->usePersonTypeQuery()
                ->filterByCode(array('clt'), \Criteria::NOT_IN)
            ->endUse()
            ->filterByActive()

            ->groupBy('Id')

            // ->distinct('Id')
            // ->groupByClass('Extia\Bundle\UserBundle\Model\Internal')
            // ->having('nbPastTasks > 0')
        ;

        $filtersForm = $this->processFilters($request, $internalsQuery);

        $sorts = $this->processSorts($request, $internalsQuery);

        $internalCollection = $this->get('knp_paginator')->paginate(
            $internalsQuery, $this->getCurrentPage($request), 10
        );

        return $this->render('ExtiaUserBundle:AdminInternal:list.html.twig', array (
            'user'      => $internal,
            'internals' => $internalCollection,
            'form'      => $filtersForm->createView(),
            'sort'      => $sorts
        ));
    }

    /**
     * renders a new consultant form
     *
     * @param Request $request
     *
     * @return Response
     */
    public function newAction(Request $request)
    {
        $internal = new Internal();

        return $this->renderForm(
            $request,
            $internal,
            'ExtiaUserBundle:AdminInternal:new.html.twig'
        );
    }

    /**
     * renders an edit form for given user id
     *
     * @param Request $request
     * @param int     $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return Response
     */
    public function editAction(Request $request, Internal $internal)
    {
        return $this->renderForm(
            $request,
            $internal,
            'ExtiaUserBundle:AdminInternal:edit.html.twig'
        );
    }

    /**
     * executes form on given consultant and renders it on given template
     *
     * @param Request    $request
     * @param Consultant $consultant
     * @param string     $template
     *
     * @return Response
     */
    public function renderForm(Request $request, Internal $internal, $template)
    {
        $user = $this->getUser();
        if (!$this->get('security.context')->isGranted('ROLE_INTERNAL_WRITE', $user)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to write internals.'));
        }

        $isNew = $internal->isNew();

        // default under the root node
        $parentId = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->select('Id')
            ->findRoot();

        if (!$isNew) {
            $parent = $internal->getParent();
            if (!empty($parent)) {
                $parentId = $parent->getId();
            }
        }

        $form = $this->get('form.factory')
            ->create('internal_form', $internal, array(
                'internal_id' => $isNew ? null : $internal->getId(),
                'with_resign' => !$isNew
            ))
        ;

        // unmapped data
        $form->get('parent')->setData($parentId);

        if ($request->request->has($form->getName()) &&
            $this->get('extia_user.admin.internal_form_handler')->handle($form, $request) ) {

            if ($isNew) { // redirect on edit if was new

                return $this->redirect($this->get('router')->generate(
                    'UserBundle_internal_edit', $internal->getRouting()
                ));
            }
        }

        return $this->render($template, array(
            'internal' => $internal,
            'form'     => $form->createView()
        ));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listAjaxAction(Request $request)
    {
        $value = $request->get('q');

        // TODO Manque le rebase pour la recherche
        $managers = InternalQuery::create()
                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                    ->joinWith('PersonType')
                        ->usePersonTypeQuery()
                            ->filterByCode('ia')
                        ->endUse()
                    ->filterByUrl('%' . $value . '%')
                    ->find();

        $json = array ();
        foreach ($managers as $manager) {
            $json[] = array (
                'id'   => $manager->getId(),
                'name' => $manager->getFirstname() . ' ' . $manager->getLastname()
            );
        }

        return JsonResponse::create($json);
    }

}
