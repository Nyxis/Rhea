<?php

namespace Extia\Workflow\MissionMonitoringBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;

/**
 * form handler for appointement node
 * @see Extia/Workflow/MissionMonitoringBundle/Resources/workflows/meeting.xml
 */
class MeetingNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        $nextMeeting = $this->temporalTools->changeDate(
            $task->getActivationDate(), '+2 months'
        );

        $task->data()->set('next_meeting_date',
            $this->temporalTools->findNextWorkingDay($nextMeeting, 'U')
        );

        $task->data()->set('notif_date', $this->temporalTools->findNextWorkingDay(
            $this->temporalTools->findNextWorkingDay(
                $this->temporalTools->changeDate($nextMeeting, '-7 days'), 'U'
            )
        );

        $task->addDocument($data['mission_meeting_doc']);

        // reports
        $report = $data['report'];
        $report->setTask($task);
        $report->setMissionOrder(
            $task->getTarget('consultant')->getCurrentMissionOrder()
        );

        $task->save($pdo);

        // notify next node
        return $this->notifyNext('appointement', $task, array(), $pdo);
    }
}
