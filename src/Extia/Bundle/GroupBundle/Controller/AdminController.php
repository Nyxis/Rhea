<?php

namespace Extia\Bundle\GroupBundle\Controller;

use Extia\Bundle\GroupBundle\Model\Group;
use Extia\Bundle\GroupBundle\Model\GroupQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * controller for group admin
 */
class AdminController extends Controller
{
    /**
     * list all groups
     * @param  Request  $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        $locale = $request->attributes->get('_locale');

        $groupCollection = GroupQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->distinct('Group.id')
            ->joinWith('GroupCredential', \Criteria::LEFT_JOIN)
            ->joinWith('GroupCredential.Credential', \Criteria::LEFT_JOIN)
            ->useGroupCredentialQuery(null, \Criteria::LEFT_JOIN)
                ->useCredentialQuery(null, \Criteria::LEFT_JOIN)
                    ->joinWithI18n($locale)
                ->endUse()
            ->endUse()
            ->orderBy('Label')
            ->find();

        return $this->render('ExtiaGroupBundle:Admin:list.html.twig', array(
            'groups' => $groupCollection
        ));
    }

    /**
     * creates a new group
     * @param  Request  $request
     * @return Response
     */
    public function newAction(Request $request)
    {
        $group = new Group();

        return $this->renderForm(
            $request, $group, 'ExtiaGroupBundle:Admin:new.html.twig'
        );
    }

    /**
     * edit given group
     * @param  Request  $request
     * @return Response
     */
    public function editAction(Request $request, $groupId)
    {
        $locale = $request->attributes->get('_locale');

        $group = GroupQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterById($groupId)
            ->distinct('Group.id')
            ->joinWith('GroupCredential')
            ->joinWith('GroupCredential.Credential')
            ->useGroupCredentialQuery()
                ->useCredentialQuery()
                    ->joinWithI18n($locale)
                ->endUse()
            ->endUse()
            ->find()
            ->getFirst();

        if (empty($group)) {
            throw new NotFoundHttpException(sprintf('Any group found for given id, "%s" given.', $groupId));
        }

        return $this->renderForm(
            $request, $group, 'ExtiaGroupBundle:Admin:edit.html.twig'
        );
    }

    /**
     * executes form on given group and renders it on given template
     * @param  Group    $group
     * @param  string   $template
     * @return Response
     */
    protected function renderForm(Request $request, Group $group, $template)
    {
        $form = $this->get('form.factory')->create('group_form', $group, array(
            'group_id' => $group->getId()
        ));

        $isNew = $group->isNew();

        if ($request->request->has($form->getName())) {
            if ($this->get('extia_group.form.group_handler')->handle($form, $request)) {

                // success message
                $this->get('notifier')->add(
                    'success', 'group.admin.notification.save_success',
                    array('%group_label%' => $group->getLabel())
                );

                // redirect on edit if was new
                if ($isNew) {
                    return $this->redirect($this->get('router')->generate(
                        'GroupBundle_edit', array('groupId' => $group->getId())
                    ));
                }
            }
        }

        return $this->render($template, array(
            'group'   => $group,
            'form'    => $form->createView(),
            'locales' => $this->container->getParameter('extia_group.managed_locales')
        ));
    }

    /**
     * delete given group
     * @param  Request  $request
     * @return Response
     */
    public function deleteAction(Request $request, $groupId)
    {
        $group = GroupQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->findPk($groupId);

        if (empty($group)) {
            throw new NotFoundHttpException(sprintf('Any group found for given id, "%s" given.', $groupId));
        }

        try {
            $group->delete();
            $this->get('notifier')->add('success', 'group.admin.notification.delete_success', array('%group_label%' => $group->getLabel()));
        } catch (\Exception $e) {
            if ($this->container->getParameter('kernel.debug')) {
                throw $e;
            }

            $this->get('logger')->err($e->getMessage());
            $this->get('notifier')->add('success', 'group.admin.notification.delete_error', array('%group_label%' => $group->getLabel()));
        }

        return $this->redirect(
            $this->get('router')->generate('GroupBundle_list')
        );
    }
}
