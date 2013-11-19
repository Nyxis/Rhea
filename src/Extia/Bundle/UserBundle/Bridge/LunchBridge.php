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
            ->find($pdo)->getFirst();

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
                'next_date'      => $currentTask->findNextWorkingDay(
                        (int) $currentTask->calculateDate($consultant->getCurrentMissionOrder()->getBeginDate(), '+2 months', 'U')
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
}
