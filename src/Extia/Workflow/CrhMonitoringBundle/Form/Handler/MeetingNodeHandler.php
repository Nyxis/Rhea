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
        $nextMeeting = $this->temporalTools->changeDate($task->getActivationDate(), '+3 months')

        $task->data()->set('next_meeting_date',
            $this->temporalTools->findNextWorkingDay($nextMeeting)
        );

        $task->data()->set('notif_date',
            $this->temporalTools->findNextWorkingDay(
                $this->temporalTools->changeDate($nextMeeting, '-7 days')
            )
        );

        $task->addDocument($data['crh_meeting_doc']);

        $task->save($pdo);

        // notify next node
        return $this->notifyNext('appointement', $task, array(), $pdo);
    }
}
