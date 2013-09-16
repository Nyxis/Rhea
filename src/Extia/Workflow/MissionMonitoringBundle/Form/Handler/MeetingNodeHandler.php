<?php

namespace Extia\Workflow\MissionMonitoringBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * form handler for appointement node
 * @see Extia/Workflow/MissionMonitoringBundle/Resources/workflows/meeting.xml
 */
class MeetingNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, Request $request, \Pdo $pdo = null)
    {
        $nextMeetingTmstp = $task->calculateDate($task->getActivationDate(), '+'.$data['next_meeting'].' months', 'U');

        $task->data()->set('meeting_date', $task->findNextWorkingDay($nextMeetingTmstp));
        $task->data()->set('notif_date', $task->findNextWorkingDay(
            (int) $task->calculateDate($nextMeetingTmstp, '-7 days', 'U'))
        );

        $task->addDocument($data['crh_meeting_doc']);

        $task->save($pdo);

        // notify next node
        return $this->notifyNext('appointement', $task, $request, $pdo);
    }
}
