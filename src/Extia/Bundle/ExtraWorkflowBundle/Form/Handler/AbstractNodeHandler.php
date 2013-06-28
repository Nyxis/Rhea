<?php

namespace Extia\Bundle\ExtraWorkflowBundle\Form\Handler;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\Task;

use EasyTask\Bundle\WorkflowBundle\Workflow\Aggregator;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
/**
 * Base class for node handlers
 * @see Extia/Bundles/ExtraWorkflowBundle/Resources/config/services.xml
 */
abstract class AbstractNodeHandler
{
    /**
     * @var Aggregator
     */
    protected $workfows;

    /**
     * set workflows list
     * @param Aggregator $workflows [description]
     */
    public function setWorkflows(Aggregator $workflows)
    {
        $this->workflows = $workflows;
    }

    /**
     * handling method
     * @param  Form     $form
     * @param  Request  $request
     * @param  Task     $task
     * @return Response | null
     */
    abstract public function handle(Form $form, Request $request, Task $task);

    /**
     * find next working day
     * @param  timestamp $timestamp
     * @return timestamp
     */
    protected function findNextWorkingDay($timestamp)
    {
        $workingDays = range(1,5);
        $offDays     = range(6,7);

        while (in_array(date('N', $timestamp), $offDays)) {
            $timestamp += 3600*24;
        }

        return $timestamp;
    }

    /**
     * remove days to given date
     * @param  timestamp $date
     * @param  int       $nbDays
     * @return timestamp
     */
    protected function removeDays($date, $nbDays)
    {
        return $date - ($nbDays*24*3600);
    }

    /**
     * adds $nbMonths to given date
     * @param  timestamp $date
     * @param  int       $nbMonths
     * @return timestamp
     */
    protected function addMonths($date, $nbMonths)
    {
        $dateMonth  = date('n', $date);

        // adds select month / year
        $nextDateMonth = $dateMonth + $nbMonths;

        $nextDateYear  = $nextDateMonth > 12 ? date('Y', $date) + floor($nextDateMonth/12) : date('Y', $date);
        $nextDateMonth = $nextDateMonth > 12 ? $nextDateMonth % 12 : $nextDateMonth;

        // recreate date
        $nextDateTmstp = mktime(
            0, 0, 0, // on midnight
            $nextDateMonth, date('j', $date), $nextDateYear
        );

        return $nextDateTmstp;
    }
}
