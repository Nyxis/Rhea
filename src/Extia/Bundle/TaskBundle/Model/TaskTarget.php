<?php

namespace Extia\Bundle\TaskBundle\Model;

use Extia\Bundle\TaskBundle\Workflow\TaskTargetQueryInterface;

use Extia\Bundle\TaskBundle\Model\om\BaseTaskTarget;

class TaskTarget extends BaseTaskTarget
{
    /**
     * returns related target query
     * @return TaskTargetQueryInterface
     */
    public function getTargetQuery()
    {
        $query = \PropelQuery::from($this->getTargetModel());
        if (!$query instanceof TaskTargetQueryInterface) {
            throw new \LogicException('Query class "'.get_class($query).'" doesnt implements TaskTargetQueryInterface.');
        }

        return $query;
    }

    /**
     * loads related task target
     *
     * @param  Pdo                 $pdo
     * @return TaskTargetInterface
     */
    public function getTarget(\Pdo $pdo = null)
    {
        return $this->getTargetQuery()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterById($this->getTargetId())
            ->filterForTasks($this->getTask())
            ->findOne($pdo);
    }
}
