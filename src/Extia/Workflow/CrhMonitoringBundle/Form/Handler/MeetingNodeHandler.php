<?php

namespace Extia\Workflow\CrhMonitoringBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;

/**
 * form handler for appointement node
 * @see Extia/Workflow/CrhMonitoringBundle/Resources/workflows/meeting.xml
 */
class MeetingNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        $nextMeetingTmstp = $task->calculateDate($task->getActivationDate(), '+3 months', 'U');

        $task->data()->set('next_meeting_date', $task->findNextWorkingDay($nextMeetingTmstp));
        $task->data()->set('notif_date', $task->findNextWorkingDay(
            (int) $task->calculateDate($nextMeetingTmstp, '-7 days', 'U'))
        );

        $task->addDocument($data['crh_meeting_doc']);

        $task->save($pdo);

        // notify next node
        return $this->notifyNext('appointement', $task, array(), $pdo);
    }
}
