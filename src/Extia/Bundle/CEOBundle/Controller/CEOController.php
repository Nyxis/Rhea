<?php

namespace Extia\Bundle\CEOBundle\Controller;

use Extia\Bundle\TaskBundle\Model\TaskQuery;
use Extia\Bundle\UserBundle\Model\AgencyQuery;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;
use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\PersonQuery;
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
            ->select(array('id'))
            ->count();

        $data['nbMissions'] = $data['nbConsultants'] - $data['nbIntercontrats'];

        // Nombre de taches en retard
        $data['nbLateTasks'] = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWithCurrentNodes()
            ->filterByCompletionDate(array('max' => 'now'))
            ->select(array('id'))
            ->count();

        // Nombre de taches total
        $data['nbTotalTasks'] = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWithCurrentNodes()
            ->select(array('id'))
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
            ->select(array('id'))
            ->count();
            if(empty($slowestAgency['Tasks']) || $slowestAgency['Tasks'] < $nbLateTasks )
            {
                $slowestAgency['agency'] = $agency;
                $slowestAgency['tasks'] = $nbLateTasks;
            }
        }
        return $slowestAgency;
    }

    public function getInternalWithLateTasksAction(Request $request, $internalType)
    {
        if($internalType == 3) {
            $label = "CRH";
        } else {
            $label = "Managers";
        }

        $internals = PersonQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByPersonTypeId($internalType)
            ->useTaskRelatedByAssignedToQuery()
                ->filterByCompletionDate(array('max' => 'now'))
                ->useNodeQuery()
                    ->filterByCurrent(true)
                    ->filterByEnded(false)
                ->endUse()
            ->endUse()
            ->joinwithTaskRelatedByAssignedTo()

            ->find()->toArray();

        usort($internals, array($this, "orderByLateTasks"));

        $internalsLateTasks = array();

        $internals_ = array();

        foreach($internals as $key => $internal)
        {
            $internalsLateTasks[$internal['Id']]['lateTasksRelatedByAssignedTo'] = $internals[$key]['TasksRelatedByAssignedTo'];
            $internalsLateTasks[$internal['Id']]['cumulateTime'] = 0;
            $new_manager = InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterById($internal['Id'])
                ->innerJoin('Person')
                ->findOne();
            $internals_[] = $new_manager;
        }

        unset($managers);

        $now = new \DateTime();

        foreach($internalsLateTasks as $idManager => $value) {
            foreach ($value['lateTasksRelatedByAssignedTo'] as $task) {
                $internalsLateTasks[$idManager]['cumulateTime'] += $now->diff($task['CompletionDate'])->days;
            }
        }


        return $this->render('ExtiaCEOBundle:CEO:internals_late.html.twig', array(
            'internals' => $internals_,
            'lastTasks' => $internalsLateTasks,
            'label' => $label
        ));
    }




    public function orderByLateTasks($a, $b) {
        return count($b["TasksRelatedByAssignedTo"]) - count($a["TasksRelatedByAssignedTo"]);
    }

}
