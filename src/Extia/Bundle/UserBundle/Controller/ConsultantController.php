<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * controller for consultant features
 */
class ConsultantController extends Controller
{
    /**
     * displays given user all task which target him as timeline
     *
     * @param Request $request
     * @param         $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Response
     */
    public function timelineAction(Request $request, $Id, $Url)
    {
        $locale = $request->attributes->get('_locale');

        $user = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterById($Id)
            ->filterByUrl($Url)

            ->joinWith('Crh')
            ->joinWith('Group', \Criteria::LEFT_JOIN)
            ->joinWith('Job')
            ->useJobQuery()
                ->joinWithI18n($locale)
            ->endUse()

            ->findOne();

        if (empty($user)) {
            throw new NotFoundHttpException(sprintf('Requested user not found : %s - id %s',
                $Url, $Id
            ));
        }

        // can access this timeline ?
        if (!$this->get('security.context')->isGranted('USER_DATA', $user)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access %s %s timeline.',
                $user->getFirstname(), $user->getLastname()
            ));
        }

        $tasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->distinct('Task.Id')
            ->joinWithAll()
            ->filterByWorkflowTypes(array_keys($this->get('workflows')->getAllowed('read')))

            ->filterByUserTargetId($user->getId())
            ->_or()
            ->filterByAssignedTo($user->getId())

            ->orderByNodeCompletion()

            ->find();

        return $this->render('ExtiaUserBundle:Consultant:timeline.html.twig', array(
            'user'  => $user,
            'tasks' => $tasks
        ));
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

        $consultantCollection = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWith('Job')
            ->useJobQuery()
                ->joinWithI18n()
            ->endUse()

            ->filterByInternalReferer($internal)

            ->find();

        return $this->render('ExtiaUserBundle:Consultant:list.html.twig', array(
            'user'        => $internal,
            'consultants' => $consultantCollection
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

        return $this->renderForm($request, $consultant, 'ExtiaUserBundle:Consultant:new.html.twig');
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
    public function editAction(Request $request, $Id, $Url)
    {
        $consultant = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWith('Job')
            ->useJobQuery()
                ->joinWithI18n()
            ->endUse()
            ->filterByUrl($Url)
            ->findPk($Id);

        if (empty($consultant)) {
            throw new NotFoundHttpException(sprintf('Any consultant found for given id/url, "%s/%s" given.', $Id, $Url));
        }

        return $this->renderForm($request, $consultant, 'ExtiaUserBundle:Consultant:edit.html.twig');
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
        $form  = $this->get('form.factory')->create('consultant', $consultant, array());
        $isNew = $consultant->isNew();

        if ($request->request->has($form->getName())) {
            if ($this->get('extia_group.form.group_handler')->handle($form, $request)) {

                // success message
                $this->get('notifier')->add(
                    'success', 'consultant.admin.notification.save_success',
                    array('%consultant_name%' => $consultant->getLongName())
                );

                // redirect on edit if was new
                if ($isNew) {
                    return $this->redirect($this->get('router')->generate(
                        'UserBundle_consultant_edit',
                        $consultant->getRouting()
                    ));
                }
            }
        }

        return $this->render($template, array(
            'consultant' => $consultant,
            'form'       => $form->createView(),
            'locales'    => $this->container->getParameter('extia_group.managed_locales')
        ));
    }

    /**
     * render all intercontract consultants for given user
     *
     * @param Request                                 $request
     * @param \Extia\Bundle\UserBundle\Model\Internal $internal
     *
     * @return Response
     */
    public function intercontractListBoxAction(Request $request, Internal $internal)
    {
        $consultantCollection = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWith('Job')
            ->useJobQuery()
                ->joinWithI18n()
            ->endUse()

            ->useConsultantMissionQuery()
                ->filterByCurrent(true)
                ->useMissionQuery()
                    ->filterByType('ic')
                ->endUse()
            ->endUse()

            ->filterByInternalReferer($internal)

            ->find();

        return $this->render('ExtiaUserBundle:Consultant:intercontract_list_box.html.twig', array(
            'internal'    => $internal,
            'consultants' => $consultantCollection
        ));
    }
}
