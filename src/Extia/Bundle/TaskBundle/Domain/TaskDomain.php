<?php

namespace Extia\Bundle\TaskBundle\Domain;

use Extia\Bundle\TaskBundle\Tools\TemporalTools;

/**
 * domain class for tasks management
 * @see ExtiaTaskBundle/Resources/config/services/domains.xml
 */
class TaskDomain
{
    protected $temporalTools;

    /**
     * construct
     * @param TemporalTools $temporalTools [description]
     */
    public function __construct(TemporalTools $temporalTools)
    {
        $this->temporalTools = $temporalTools;
    }

    /**
     * define activation period for given task
     *
     * @param  Task   $task
     * @param  mixed  $beginDate begin date (DateTime, timestamp or US format)
     * @param  string $period    period of activation from begin date (DateInterval string format)
     *
     * @return Task
     */
    public function activateTaskOn($task, $beginDate, $period = '+1 day')
    {
        $beginDate = $this->temporalTools->createDateTime($beginDate);

        // activation
        $task->setActivationDate(
            $this->temporalTools->findNextWorkingDay($beginDate)
        );

        // completion
        $this->activateTaskFor($task, $period);

        return $task;
    }

    /**
     * calculation completion date for given period for given task
     *
     * @param  Task   $task
     * @param  mixed  $beginDate begin date (DateTime, timestamp or US format)
     * @param  string $period    period of activation from begin date (DateInterval string format)
     *
     * @return Task
     */
    public function activateTaskFor($task, $period = '+1 day')
    {
        $task->setCompletionDate(
            $this->temporalTools->findNextWorkingDay(
                $this->temporalTools->changeDate($task->getActivationDate(), $period)
            )
        );

        return $task;
    }


}