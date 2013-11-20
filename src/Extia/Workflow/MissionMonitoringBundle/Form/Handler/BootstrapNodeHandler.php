<?php

namespace Extia\Workflow\MissionMonitoringBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;

/**
 * form handler for bootstrap node
 * @see Extia/Workflow/MissionMonitoringBundle/Resources/workflows/bootstrap.xml
 */
class BootstrapNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        $task->addTarget($this->loadConsultant($data['user_target_id']), $pdo);

        // activation @creation
        $this->taskDomain->activateTaskOn(
            $task, date('Y-m-d'), '+1 day'
        );

        // activate before given date for pre-notification
        $task->data()->set('next_meeting_date',
            $this->temporalTools->findNextWorkingDay($data['next_date'], 'U')
        );
        $task->data()->set('notif_date',
            $this->temporalTools->findNextWorkingDay(
                $this->temporalTools->changeDate($data['next_date'], '-7 days'), 'U'
            )
        );

        // assignation
        if (!empty($data['assigned_to'])) {
            $task->setAssignedTo($data['assigned_to']);
        }

        // updates workflow fields
        $this->updateWorkflow($data, $task, $pdo);

        $task->save($pdo);

        // notify next node
        return $this->notifyNext('appointement', $task, array(), $pdo);
    }
}
