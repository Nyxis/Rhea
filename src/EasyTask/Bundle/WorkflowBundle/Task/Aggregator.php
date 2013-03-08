<?php

namespace EasyTask\Bundle\WorkflowBundle\Task;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * tasks aggregator
 * @see WorkflowBundle/Resources/config/tasks.xml
 */
class Aggregator extends ParameterBag
{
    /**
     * method called by DI, dynamically from compiler pass
     * @see EasyTask\Bundle\WorkflowBundle\DependencyInjection\Compiler\TaskAggregatorCompilerPass
     */
    public function addTask($taskId, AbstractTaskType $taskType)
    {
        return $this->add($taskId, $taskType);
    }

    /**
     * retrieve all user tasks, passed and current
     * @param  string           $username
     * @return PropelCollection
     */
    public function getTasksForUser($username)
    {
        return array();
    }
}
