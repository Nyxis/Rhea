<?php

namespace Extia\Bundle\TaskBundle\Model;

use Extia\Bundle\TaskBundle\Workflow\TaskTargetInterface;
use Extia\Bundle\TaskBundle\Model\om\BaseTaskQuery;

class TaskQuery extends BaseTaskQuery
{
    /**
     * adds joins and filter on workflow node table
     * @return TaskQuery
     */
    public function joinWithCurrentNodes()
    {
        return $this->useNodeQuery()
                ->filterByCurrent(true)
                ->filterByEnded(false)
            ->endUse()
            ->joinWith('Node')
            ->joinWith('Node.Workflow');
    }

    /**
     * filter query on given workflow types
     * @param  array     $workflowTypes
     * @return TaskQuery
     */
    public function filterByWorkflowTypes(array $workflowTypes)
    {
        return $this->useNodeQuery()
            ->useWorkflowQuery()
                ->filterByType($workflowTypes)
            ->endUse()
        ->endUse();
    }

    /**
     * performs all used joins for tasks
     * @return TaskQuery
     */
    public function joinWithAll()
    {
        return $this->joinWith('Node')
            ->joinWith('Node.Workflow')
            ->joinWith('Comment', \Criteria::LEFT_JOIN);
    }

    /**
     * execute current select, and loads target
     *
     * @param  Pdo              $pdo opt pdo connection
     * @return PropelCollection
     */
    public function findWithTargets(\Pdo $pdo = null)
    {
        $collection = $this
            ->joinWith('UserAssigned', \Criteria::LEFT_JOIN)
            ->joinWith('TaskTarget')
            ->find($pdo);

        // build a mapping
        $targetData = array();
        foreach ($collection as $task) {
            $taskTargets = $task->getTaskTargets();
            foreach ($taskTargets as $taskTarget) {
                $targetModel = $taskTarget->getTargetModel();
                $targetId    = $taskTarget->getTargetId();

                // class init
                if (!isset($targetData[$targetModel])) {
                    $targetData[$targetModel] = array(
                        'query'   => $taskTarget->getTargetQuery(),
                        'ids'     => array(),
                        'mapping' => array()
                    );
                }

                $targetData[$targetModel]['ids'][]              = $targetId;
                $targetData[$targetModel]['mapping'][$targetId] = $task->getId();
            }
        }

        // performs queries
        $targetsForTasks = array();
        foreach ($targetData as $modelClass => $modelData) {
            $query            = $modelData['query'];
            $targetCollection = $query
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterById($modelData['ids'])
                ->filterForTasks()
                ->find($pdo)
            ;

            foreach ($targetCollection as $targetObject) {
                $targetsForTasks[$modelData['mapping'][$targetObject->getPrimaryKey()]][] = $targetObject;
            }
        }

        // reinject into tasks
        foreach ($collection as $task) {
            if (!empty($targetsForTasks[$task->getId()])) {
                $task->setTargets($targetsForTasks[$task->getId()]);
            }
        }

        return $collection;
    }

    /**
     * filters query on given target
     *
     * @param  TaskTargetInterface $targetObject
     * @return TaskQuery
     */
    public function filterByTarget(TaskTargetInterface $targetObject)
    {
        return $this
            ->useTaskTargetQuery()
                ->filterByTargetModel(get_class($targetObject))
                ->filterByTargetId($targetObject->getPrimaryKey())
            ->endUse()
        ;
    }
}
