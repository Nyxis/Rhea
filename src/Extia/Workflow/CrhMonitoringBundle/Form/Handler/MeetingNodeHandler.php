<?php

namespace Extia\Workflow\CrhMonitoringBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * form handler for appointement node
 * @see Extia/Workflow/CrhMonitoringBundle/Resources/workflows/meeting.xml
 */
class MeetingNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function handle(Form $form, Request $request, Task $task)
    {
        $form->bind($request);
        if (!$form->isValid()) {
            return false;
        }

        // updates task with incomming data
        $data = $form->getData();

        $nextMeetingTmstp = $task->calculateDate($task->getActivationDate(), '+'.$data['next_meeting'].' months', 'U');

        $task->data()->set('meeting_date', $task->findNextWorkingDay($nextMeetingTmstp));
        $task->data()->set('notif_date', $task->findNextWorkingDay(
            (int) $task->calculateDate($nextMeetingTmstp, '-7 days', 'U'))
        );

        $task->addDocument($data['crh_meeting_doc']);

        $task->save();

        // notify next node
        $workflow = $task->getNode()->getWorkflow();

        return $this->workflows
            ->getNode($workflow, 'appointement')
            ->notify($workflow, $request);
    }
}
