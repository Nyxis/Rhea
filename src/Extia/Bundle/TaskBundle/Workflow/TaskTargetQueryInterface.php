<?php

namespace Extia\Bundle\TaskBundle\Workflow;

/**
 * interface to implements on task target queries
 */
interface TaskTargetQueryInterface
{
    /**
     * filters current query for tasks, hook called after task target
     * loading, use it for custom target retrieving
     *
     * @return TargetQueryInterface
     */
    public function filterForTasks();
}
