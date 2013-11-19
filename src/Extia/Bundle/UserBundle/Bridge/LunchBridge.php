<?php

namespace Extia\Bundle\UserBundle\Bridge;

use Extia\Bundle\MissionBundle\Model\Mission;
use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\TaskBundle\Workflow\TaskTargetInterface;
use Extia\Bundle\TaskBundle\Model\TaskQuery;
use Extia\Bundle\TaskBundle\Model\Task;

class LunchBridge extends AbstractTaskBridge {

    /**
     * @see AbstractTaskBridge::getBridgedWorkflow()
     */
    protected function getBridgedWorkflow()
    {
        return 'lunch';
    }

    /**
     * get lunch task with a given target
     *
     * @param TaskTargetInterface $target
     * @param $pdo
     * @return Task
     */
    public function getLunchTask(TaskTargetInterface $target, $pdo)
    {
        $task = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->useNodeQuery()
                ->filterByCurrent(true)
                ->useWorkflowQuery()
                    ->filterByType($this->getBridgedWorkflow())
                ->endUse()
            ->endUse()
            ->filterByTarget($target)
            ->joinWith('Node')
            ->joinWith('Node.Workflow')
            ->findOne($pdo);

        return $task;
    }

    /**
     * Creates and init lunch for given consultant and given mission
     *
     * @param Consultant $consultant
     * @param Mission    $mission
     * @param \Pdo       $pdo
     */
    public function createLunch(Consultant $consultant, Mission $mission, \Pdo $pdo = null)
    {
        $currentTask  = $this->createWorkflow(array(), $pdo);

        // bootstrap task
        return $this->resolveNode($currentTask, array(

                // workflow data
                'workflow' => array(
                    'name' => $this->translator->trans('lunch.default_name', array(
                            '%mission_target%' => $mission->getFullLabel(),
                            '%mission%'     => $mission->getClient()->getTitle()
                        )),
                    'description' => $this->translator->trans('lunch.default_desc', array(
                            '%mission_target%' => $mission->getFullLabel(),
                            '%mission%'     => $mission->getClient()->getTitle()
                        ))
                ),

                // bootstrap data
                'mission_target_id' => $mission->getId(),
                'assigned_to'    => $mission->getManagerId(),
                'next_date'      => $this->temporalTools->findNextWorkingDay(
                    $this->temporalTools->changeDate($consultant->getCurrentMissionOrder()->getBeginDate(), '+2 months', 'U')
                )
            ),
            $pdo
        );
    }

    /**
     * Close lunch of given task
     *
     * @param Task  $task
     * @param \Pdo  $pdo
     * @return Task
     */
    public function closeLunch(Task $task, \Pdo $pdo = null)
    {
        if (!empty($task))
        {
            $node = $task->getNode();
            $node->setEnded(true);
            $node->setCurrent(false);
            $node->save($pdo);
        }

        return $task;
    }


    /**
     *  Update lunch task data on mission order creation :
     *      - Add consultant to lunch targets if lunch exist
     *      - Create lunch with consultant as target if lunch doesn't exist
     *
     * @param Consultant $consultant
     * @param Mission    $mission
     * @Param \Pdo       $pdo
     */
    public function updateLunchOnMissionOrderCreation(Consultant $consultant, Mission $mission, \Pdo $pdo = null)
    {
        $task = $this->getLunchTask($mission, $pdo);

        if (!empty($task))
        {
            $task->addTarget($consultant);
            $task->save();
        }
        else
        {
            if ($mission->getType() == 'client')
            {
                $this->createLunch($consultant, $mission, $pdo);
            }
        }
    }

    /**
     * Close lunch task on mission order deletion
     *      - If $consultant is the last consultant of the lunch, we delete the target
     *      - We close the lunch
     *
     * @param Consultant $consultant
     * @param Mission $mission
     * @param \Pdo $pdo
     */
    public function closeLunchOnMissionOrderDeletion(Consultant $consultant, \Pdo $pdo = null)
    {
        $task = $this->getLunchTask($consultant, $pdo);

        if (!empty($task))
        {
            if ($task->getTaskTargets()->count() == 2)
            {
                $this->closeLunch($task, $pdo);
            }

            $task->removeTarget($consultant, $pdo);
            $task->save($pdo);
        }
    }
}
