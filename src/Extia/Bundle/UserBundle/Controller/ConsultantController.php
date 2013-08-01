<?php

namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

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
     * @param  Request  $request
     * @return Response
     */
    public function timelineAction(Request $request, $userId, $firstname, $lastname)
    {
        $locale = $request->attributes->get('_locale');

        $user = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterById($userId)
            ->filterByFirstname($firstname)
            ->filterByLastname($lastname)

            ->joinWith('Crh')
            ->joinWith('Group', \Criteria::LEFT_JOIN)
            ->joinWith('Job')
            ->useJobQuery()
                ->joinWithI18n($locale)
            ->endUse()

            ->findOne();

        if (empty($user)) {
            throw new NotFoundHttpException(sprintf('Requested user not found : %s %s (id %s)',
                $firstname, $lastname, $userId
            ));
        }

        // can access this timeline ?
        if (!$this->get('security.context')->isGranted('USER_DATA', $user)) {
            throw new AccessDeniedHttpException(sprintf('You have any credentials to access %s %s timeline.',
                $firstname, $lastname
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

        return $this->render('ExtiaUserBundle:Consultant:consultant_tasks.html.twig', array(
            'user'  => $user,
            'tasks' => $tasks
        ));
    }
}
