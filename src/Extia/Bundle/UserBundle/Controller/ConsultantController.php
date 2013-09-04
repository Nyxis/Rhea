<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;
use Extia\Bundle\UserBundle\Model\MissionOrderQuery;

use Extia\Bundle\TaskBundle\Model\TaskQuery;
use Extia\Bundle\DocumentBundle\Model\DocumentQuery;

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
        // can access this timeline ?
        if (!$this->get('security.context')->isGranted('ROLE_CONSULTANT_READ', $this->getUser())) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access consultants timeline.'));
        }

        $user = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterById($Id)
            ->filterByUrl($Url)

            ->joinWith('Crh')
            ->joinWith('Group', \Criteria::LEFT_JOIN)

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

            ->orderByActivationDate(\Criteria::DESC)
            ->useNodeQuery()
                ->orderByCurrent(\Criteria::DESC)
            ->endUse()

            ->find();

        return $this->render('ExtiaUserBundle:Consultant:timeline.html.twig', array(
            'user'  => $user,
            'tasks' => $tasks
        ));
    }

    // --------------------------------------------------------
    // Components
    // --------------------------------------------------------

    /**
     * displays all document for given consultant
     *
     * @param  Request    $request
     * @param  Consultant $consultant
     * @return Response
     */
    public function documentsAction(Request $request, Consultant $consultant)
    {
        $documentsCollection = DocumentQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->orderByCreatedAt(\Criteria::DESC)
            ->usePersonTaskDocumentQuery()
                ->filterByPersonId($consultant->getId())
                ->useTaskQuery(null, \Criteria::LEFT_JOIN)
                    ->filterByWorkflowTypes(array_keys($this->get('workflows')->getAllowed('read')))
                ->endUse()
            ->endUse()
            ->find();

        return $this->render('ExtiaUserBundle:Consultant:documents.html.twig', array(
            'consultant' => $consultant,
            'documents'  => $documentsCollection
        ));
    }

    /**
     * displays all missions for given consultant
     *
     * @param  Request    $request
     * @param  Consultant $consultant
     * @return Response
     */
    public function missionsAction(Request $request, Consultant $consultant)
    {
        $missionOrdersCollection = MissionOrderQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByConsultantId($consultant->getId())

            ->joinWith('Mission')
            ->joinWith('Mission.Manager')
            ->joinWith('Mission.Client')

            ->orderByBeginDate(\Criteria::DESC)
            ->find();

        return $this->render('ExtiaUserBundle:Consultant:missions.html.twig', array(
            'consultant'    => $consultant,
            'missionOrders' => $missionOrdersCollection
        ));
    }

    /**
     * displays mission switching modal and handle it for given consultant
     *
     * @param  Request    $request
     * @param  Consultant $consultant
     * @return Response
     */
    public function changeMissionAction(Request $request, Consultant $consultant)
    {
        $defaultValues = array();

        $form = $this->get('form.factory')->create('change_mission_form', $defaultValues, array());

        if ($request->request->has($form->getName())) {
            if ($this->get('extia_user.form.change_mission_handler')->handle($request, $form, $consultant)) {
                return $this->redirect($this->get('router')->generate(
                    'UserBundle_consultant_timeline', $consultant->getRouting()
                ));
            }
        }

        return $this->render('ExtiaUserBundle:Consultant:change_mission.html.twig', array(
            'consultant' => $consultant,
            'form'       => $form->createView()
        ));
    }

}
