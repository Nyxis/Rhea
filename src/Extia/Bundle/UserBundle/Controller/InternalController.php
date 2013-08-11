<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\Internal;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use Extia\Bundle\UserBundle\Model\InternalQuery;
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
     *
     * @param  Request  $request
     * @param  Internal $internal
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

    /**
     * lists all user consultants
     *
     * @param  Request $request
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
     * lists all user consultants
     *
     * @param  Request $request
     *
     * @return Response
     */
    public function teamListAction(Request $request, $page)
    {
        $internal           = $this->getUser();
        $internalCollection = InternalQuery::create()
                              ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                              ->descendantsOf($internal->getId());
        $paginator          = $this->get('knp_paginator');

        $pagination = $paginator->paginate($internalCollection, $page, 30);

        return $this->render('ExtiaUserBundle:Internal:list.html.twig', array (
            'user'      => $internal,
            'internals' => $pagination
        ));
    }

    /**
     * renders a new consultant form
     *
     * @param  Request $request
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
     * @param  Request $request
     * @param  int     $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return Response
     */
    public function editAction(Request $request, $id)
    {
        $internal = InternalQuery::create()
                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                    ->joinWith('Job')
                    ->useJobQuery()
                    ->joinWithI18n()
                    ->endUse()
                    ->findPk($id);

        if (empty($internal)) {
            throw new NotFoundHttpException(sprintf('Any consultant found for given id, "%s" given.', $id));
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
     * @param  Request    $request
     * @param  Consultant $consultant
     * @param  string     $template
     *
     * @return Response
     */
    public function renderForm(Request $request, Internal $internal)
    {
        $form  = $this->get('form.factory')->create('manager', $internal, array ());
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
