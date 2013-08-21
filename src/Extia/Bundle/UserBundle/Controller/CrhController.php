<?php
namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\ConsultantQuery;
use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\InternalQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Created by rhea.
 * @author lesmyrmidons <lesmyrmidons@gmail.com>
 * Date: 30/07/13
 * Time: 12:44
 */
class CrhController extends Controller
{

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
                              ->filterById(1)
                              ->endUse();

        $paginator          = $this->get('knp_paginator');

        $pagination = $paginator->paginate($internalCollection, $page, 20);

        return $this->render('ExtiaUserBundle:Crh:list.html.twig', array (
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

        return $this->render('ExtiaUserBundle:Crh:new.html.twig', array (
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
     * @throws NotFoundHttpException
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

        return $this->render('ExtiaUserBundle:Crh:edit.html.twig', array (
            'internal' => $internal,
            'form'     => $form->createView(),
            'locales'  => $this->container->getParameter('extia_group.managed_locales')
        ));
    }

    /**
     * executes form on given consultant and renders it on given template
     *
     * @param  Request                                $request
     * @param \Extia\Bundle\UserBundle\Model\Internal $internal
     *
     * @return Response
     */
    public function renderForm(Request $request, Internal $internal)
    {
        $form  = $this->get('form.factory')->create('crh', $internal, array ());
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
                                               'extia_user_crh_edit', array ('id' => $internal->getId())
                                           ));
                }
            }
        }

        return $form;
    }
}
