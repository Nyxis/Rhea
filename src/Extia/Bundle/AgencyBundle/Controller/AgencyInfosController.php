<?php

namespace Extia\Bundle\AgencyBundle\Controller;

use Extia\Bundle\TaskBundle\Model\TaskQuery;
use Extia\Bundle\UserBundle\Model\MissionOrder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Extia\Bundle\UserBundle\Model\ConsultantQuery;
use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\MissionOrderQuery;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AgencyInfosController extends Controller
{

    public function AgencyIds($internalAgencyId)
    {
        $agencyIdCollection = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->select(array('Id'))
            ->filterByAgencyId($internalAgencyId)
            ->find();
        return $agencyIdCollection;
    }

    public function AgencyInternals($internalAgencyId)
    {
        $internalCollection = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByAgencyId($internalAgencyId)
            ->find();

        return $internalCollection;
    }

    public function AgencyConsultantsInfos($internalAgencyId)
    {
        $nbIc = ConsultantQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->select(array('id'))
                ->useMissionOrderQuery()
                    ->filterByCurrent(true)
                    ->useMissionQuery()
                        ->filterByType('ic')
                    ->endUse()
                ->endUse()
                ->filterByActive()
                ->filterByAgencyId($internalAgencyId)
                ->count();

        $nbConsultants = ConsultantQuery::create()->filterByActive()->filterByAgencyId($internalAgencyId)->count();


        return array('nbConsultants' => $nbConsultants ,'nbActiveConsultant' => $nbConsultants - $nbIc, 'nbICConsultant' => $nbIc);
    }

    public function ActiveConsultants()
    {
        $idArray = MissionOrderQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->select(array('consultant_id'))
            ->filterByCurrent(1)
            ->find()
            ->toArray();

        return $idArray;
    }

    public function getAgencyInfosAction(Request $request, $internalAgencyId)
    {
        $internalCollection = $this->AgencyInternals($internalAgencyId);
        $consultantsInfos = $this->AgencyConsultantsInfos($internalAgencyId);

        // Nombre de taches en retard
        $nbLateTasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->select(array('id'))
            ->joinWithCurrentNodes()
            ->filterByCompletionDate(array('max' => 'now'))
            ->filterByAssignedTo($this->AgencyIds($internalAgencyId)->getData())
            ->count();

        // Nombre de taches actives total
        $nbTotalTasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->select(array('id'))
            ->joinWithCurrentNodes()
            ->filterByAssignedTo($this->AgencyIds($internalAgencyId)->getData())
            ->count();

        return $this->render('ExtiaAgencyBundle:Dashboard:agency_infos.html.twig', array(
            'internals' => $internalCollection,
            'consultants' => $consultantsInfos['nbConsultants'],
            'nbInMission' => $consultantsInfos['nbActiveConsultant'],
            'nbIc'        => $consultantsInfos['nbICConsultant'],
            'nbLateTasks' => $nbLateTasks,
            'nbTotalTasks' => $nbTotalTasks
        ));
    }


}