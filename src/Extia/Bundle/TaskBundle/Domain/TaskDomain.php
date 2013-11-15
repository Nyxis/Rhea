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
     * @param  mixed  $beginDate begin date (DateTime, timestamp or US format)
     * @param  string $period    period of activation from begin date (DateInterval string format)
     */
    public function defineActivation($task, $beginDate, $period = '+1 day')
    {
        if (is_numeric($beginDate)) {

        }
    }


}