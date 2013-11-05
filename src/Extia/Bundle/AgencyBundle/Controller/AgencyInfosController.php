<?php

namespace Extia\Bundle\AgencyBundle\Controller;

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

    public function getAgencyInternalsAction($internalAgencyId)
    {
        $internalCollection = InternalQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByAgencyId($internalAgencyId)
            ->find();

        return $internalCollection;
    }

    public function getAgencyConsultantsAction($internalAgencyId)
    {
        $consultantCollection = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByAgencyId($internalAgencyId)
            ->find();

        return $consultantCollection;
    }

    public function getActiveConsultantsAction()
    {
        $idArray = MissionOrderQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByCurrent(1)
            ->select(array('consultant_id'))
            ->find()
            ->toArray();

        return $idArray;
    }

    public function getNbInMission($consultantCollection)
    {
        $idArray = $this->getActiveConsultantsAction();
        $nbInMission = 0;
        for($i = 0;$i < count($idArray); $i++)
            foreach ($consultantCollection as $agencyConsultant)
                if ($agencyConsultant->getId() == $idArray[$i])
                    $nbInMission++;
        return $nbInMission;
    }

    public function getAgencyInfosAction(Request $request, $internalAgencyId)
    {
        $internalCollection = $this->getAgencyInternalsAction($internalAgencyId);
        $consultantCollection = $this->getAgencyConsultantsAction($internalAgencyId);
        $nbInMission = $this->getNbInMission($consultantCollection);

        return $this->render('ExtiaAgencyBundle:Dashboard:agency_infos.html.twig', array(
            'internals' => $internalCollection,
            'consultants' => $consultantCollection,
            'nbInMission' => $nbInMission
        ));
    }


}