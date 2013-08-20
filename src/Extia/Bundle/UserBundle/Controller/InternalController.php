<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\InternalQuery;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
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
        // can access this timeline ?
        if (!$this->get('security.context')->isGranted('ROLE_INTERNAL_READ', $this->getUser())) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access internal timeline.'));
        }

        $locale = $request->attributes->get('_locale');

        $user = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterById($Id)
            ->filterByUrl($Url)

            ->joinWith('Job')
            ->useJobQuery()
                ->joinWithI18n($locale)
            ->endUse()

            ->findOne();

        if (empty($user)) {
            throw new NotFoundHttpException(sprintf('Requested internal not found : %s - id %s',
                $Url, $Id
            ));
        }

        // can access this timeline ?
        if (!$this->get('security.context')->isGranted('USER_DATA', $user)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access %s %s timeline.',
                $user->getFirstname(), $user->getLastname()
            ));
        }

        return $this->render('ExtiaUserBundle:Internal:timeline.html.twig', array(
            'internal' => $user
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
            ->joinWith('Comment', \Criteria::LEFT_JOIN)
            ->joinWithTargettedUser()
            ->joinWithCurrentNodes()

            ->filterByAssignedTo($internal->getTeamIds())
            ->filterByWorkflowTypes(array_keys($this->get('workflows')->getAllowed('read')))

            ->orderByActivationDate()
            ->find();

        return $this->render('ExtiaUserBundle:Internal:team_past_tasks_box.html.twig', array (
            'tasks' => $taskCollection
        ));
    }

    // --------------------------------------------------------
    // Admin
    // --------------------------------------------------------

    /**
     * lists all user consultants
     *
     * @param Request $request
     * @param         $page
     *
     * @return Response
     */
    public function teamListAction(Request $request, $page = 1)
    {
        $internal = $this->getUser();

        $internalCollection = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->usePersonTypeQuery()
                ->filterByCode(array('crh', 'ia', 'dir'))
            ->endUse()
            ->descendantsOf($internal)
        ;

        $pagination = $this->get('knp_paginator')
            ->paginate($internalCollection, $page, 30);

        return $this->render('ExtiaUserBundle:Internal:list.html.twig', array (
            'user'      => $internal,
            'internals' => $pagination
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

    /**
     * lists all user consultants
     *
     * @param Request $request
     * @param         $page
     *
     * @return Response
     */
    public function listAction(Request $request, $page)
    {
        $internal = $this->getUser();

        $internalCollection = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWith('Job')
            ->useJobQuery()
                ->joinWithI18n()
            ->endUse()
            ->joinWith('Group')
            ->useGroupQuery()
                ->filterById(2)
            ->endUse();

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate($internalCollection, $page, 20);

        return $this->render('ExtiaUserBundle:Internal:list.html.twig', array (
            'user'      => $internal,
            'internals' => $pagination
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

        $form = $this->renderForm($request, $internal);

        return $this->render('ExtiaUserBundle:Internal:new.html.twig', array (
            'internal' => $internal,
            'form'     => $form->createView(),
            'locales'  => $this->container->getParameter('extia_group.managed_locales')
        ));
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
    public function editAction(Request $request, $Id)
    {
        $internal = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWith('Job')
            ->useJobQuery()
                ->joinWithI18n()
            ->endUse()
            ->findPk($Id);

        if (empty($internal)) {
            throw new NotFoundHttpException(sprintf('Any consultant found for given id, "%s" given.', $Id));
        }

        $form = $this->renderForm($request, $internal);

        return $this->render('ExtiaUserBundle:Internal:edit.html.twig', array (
            'internal' => $internal,
            'form'     => $form->createView(),
            'locales'  => $this->container->getParameter('extia_group.managed_locales')
        ));
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
    public function renderForm(Request $request, Internal $internal)
    {
        $form = $this->get('form.factory')->create('manager', $internal, array ());
        $isNew = $internal->isNew();

        if ($request->request->has($form->getName())) {

            if ($this->get('extia_group.form.group_handler')->handle($form, $request)) {

                // success message
                $this->get('notifier')->add(
                    'success', 'manager.admin.notification.save_success',
                    array ('%manager_name%' => $internal->getLongName())
                );

                // redirect on edit if was new
                if ($isNew) {
                    return $this->redirect($this->get('router')->generate(
                        'extia_user_manager_edit', array ('id' => $internal->getId())
                    ));
                }
            }
        }

        return $form;
    }
}
