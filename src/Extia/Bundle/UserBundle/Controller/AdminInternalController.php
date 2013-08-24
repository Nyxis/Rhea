<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\Internal;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use Extia\Bundle\UserBundle\Model\InternalQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for internal admin
 */
class AdminInternalController extends Controller
{
    /**
     * lists internals
     *
     * @param Request $request
     * @param         $page
     *
     * @return Response
     */
    public function listAction(Request $request, $page = 1)
    {
        $internal       = $this->getUser();
        $isAdminGranted = $this->get('security.context')->isGranted('ROLE_ADMIN', $internal);

        if (!$this->get('security.context')->isGranted('ROLE_INTERNAL_READ', $internal)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to internals.',
                $internal->getFirstname(), $internal->getLastname()
            ));
        }

        $internalCollection = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->usePersonTypeQuery()
                ->filterByCode(array('clt'), \Criteria::NOT_IN)
            ->endUse()
            // ->filterByEndContractDate(null, \Criteria::ISNULL) // @todo
            ->_if(!$isAdminGranted)
                ->descendantsOf($internal)
            ->_endif()
            ->orderByTreeLeft()
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
