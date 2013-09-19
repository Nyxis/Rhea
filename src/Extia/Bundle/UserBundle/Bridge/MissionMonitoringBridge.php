<?php

namespace Extia\Bundle\UserBundle\Bridge;

use Extia\Bundle\UserBundle\Consultant;

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
        $currentTask = $this->createWorkflow(array(), $pdo);

        // bootstrap task
        return $this->resolveNode($task, array(

                // workflow data
                'workflow' => array(
                    'name' => $this->translator->trans('mission_monitoring.default_name', array(
                        '%user_target%' => $consultant->getLongName(),
                        '%mission%'     => $consultant->getCurrentMission()->getClient()->getTitle()
                    )),
                    'description' => $this->translator->trans('mission_monitoring.default_desc', array(
                        '%user_target%' => $consultant->getLongName(),
                        '%mission%'     => $consultant->getCurrentMission()->getClient()->getTitle()
                    ))
                ),

                // bootstrap data
                'user_target_id' => $consultant->getId(),
                'next_date'      => $task->findNextWorkingDay(
                    (int) $task->calculateDate($consultant->getCurrentMissionOrder()->getBeginDate(), '+7 days', 'U')
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
            ->joinWith('Node')
            ->joinWith('Node.Workflow')

            ->useNodeQuery()
                ->filterByCurrent(true)
                ->useWorkflowQuery()
                    ->filterByType($this->getBridgedWorkflow())
                ->endUse()
            ->endUse()
            ->filterByTargetUserId($consultant->getId())

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
