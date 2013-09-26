<?php

namespace Extia\Bundle\UserBundle\Bridge;

use Extia\Bundle\UserBundle\Model\Consultant;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

/**
 * bridge to mission monitoring bundle
 *
 * @see Extia/Bundles/UserBundle/Resources/config/bridges.xml
 */
class MissionMonitoringBridge extends AbstractTaskBridge
{
    /**
     * @see AbstractTaskBridge::getBridgedWorkflow()
     */
    protected function getBridgedWorkflow()
    {
        return 'mission_monitoring';
    }

    /**
     * creates and init mission monitoring for given consultant
     *
     * @param Consultant $consultant
     * @param \Pdo       $pdo
     */
    public function createMonitoring(Consultant $consultant, \Pdo $pdo = null)
    {
        $currentTask  = $this->createWorkflow(array(), $pdo);
        $missionOrder = $consultant->getCurrentMissionOrder($pdo);

        // bootstrap task
        return $this->resolveNode($currentTask, array(

                // workflow data
                'workflow' => array(
                    'name' => $this->translator->trans('mission_monitoring.default_name', array(
                        '%user_target%' => $consultant->getLongName(),
                        '%mission%'     => $missionOrder->getMission()->getClient()->getTitle()
                    )),
                    'description' => $this->translator->trans('mission_monitoring.default_desc', array(
                        '%user_target%' => $consultant->getLongName(),
                        '%mission%'     => $missionOrder->getMission()->getClient()->getTitle()
                    ))
                ),

                // bootstrap data
                'user_target_id' => $consultant->getId(),
                'assigned_to'    => $missionOrder->getMission()->getManagerId(),
                'next_date'      => $currentTask->findNextWorkingDay(
                    (int) $currentTask->calculateDate($missionOrder->getBeginDate(), '+7 days', 'U')
                )
            ),
            $pdo
        );
    }

    /**
     * close all mission monitoring for given consultant
     *
     * @param  Consultant $consultant
     * @param  \Pdo       $pdo
     * @return int        number of closed workflows
     */
    public function closeMonitorings(Consultant $consultant, \Pdo $pdo = null)
    {
        $tasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))

            ->useNodeQuery()
                ->filterByCurrent(true)
                ->useWorkflowQuery()
                    ->filterByType($this->getBridgedWorkflow())
                ->endUse()
            ->endUse()
            ->filterByTarget($consultant)

            ->joinWith('Node')
            ->joinWith('Node.Workflow')

            ->find($pdo)
        ;

        foreach ($tasks as $task) {
            $node = $task->getNode();
            $node->setEnded(true);
            $node->setCurrent(false);
            $node->save($pdo);
        }

        return $tasks->count();
    }
}
