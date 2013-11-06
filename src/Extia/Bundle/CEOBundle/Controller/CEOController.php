<?php

namespace Extia\Bundle\CEOBundle\Controller;

use Extia\Bundle\TaskBundle\Model\TaskQuery;
use Extia\Bundle\UserBundle\Model\AgencyQuery;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;
use Extia\Bundle\UserBundle\Model\InternalQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


class CEOController extends Controller
{
    public function CompanyInformationsAction(Request $request)
    {

        $data = array();

        $data['nbInternals'] = InternalQuery::create()->count();

        $data['nbConsultants'] = ConsultantQuery::create()->filterByActive()->count();
        $data['nbIntercontrats'] = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->useMissionOrderQuery()
                ->filterByCurrent(true)
                ->useMissionQuery()
                    ->filterByType('ic')
                ->endUse()
            ->endUse()
            ->filterByActive()
            ->count();

        $data['nbMissions'] = $data['nbConsultants'] - $data['nbIntercontrats'];

        // Nombre de taches en retard
        $data['nbLateTasks'] = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWithCurrentNodes()
            ->filterByCompletionDate(array('max' => 'now'))
            ->count();

        // Nombre de taches total
        $data['nbTotalTasks'] = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWithCurrentNodes()
            ->count();

        $data['slowestAgency'] = $this->getAgencyWithMostLateTasks();

        return $this->render('ExtiaCEOBundle:CEO:company_infos.html.twig', array(
            'data' => $data
        ));
    }

    public function getAgencyWithMostLateTasks()
    {

        $agencyArray = AgencyQuery::create()->find()->toArray();

        $slowestAgency = array();
        foreach($agencyArray as $agency)
        {
            $agencyIdCollection = InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->select(array('Id'))
                ->filterByAgencyId($agency['Id'])
                ->find();

            $nbLateTasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWithCurrentNodes()

            ->filterByAssignedTo($agencyIdCollection->getData())
            ->filterByWorkflowTypes(array_keys($this->get('workflows')->getAllowed('read')))

            ->filterByCompletionDate(array('max' => 'now'))

            ->orderByActivationDate()
            ->count();
            if(empty($slowestAgency['Tasks']) || $slowestAgency['Tasks'] < $nbLateTasks )
            {
                $slowestAgency['agency'] = $agency;
                $slowestAgency['tasks'] = $nbLateTasks;
            }
        }
        return $slowestAgency;
    }
}
