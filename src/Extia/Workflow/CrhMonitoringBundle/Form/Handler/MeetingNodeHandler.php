<?php

namespace Extia\Workflow\CrhMonitoringBundle\Form\Handler;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\Task;
use Extia\Bundle\ExtraWorkflowBundle\Form\Handler\AbstractNodeHandler;

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

        // calculate next meeting date
        $meetingDate   = $task->getActivationDate()->format('U');

        $meetingMonth  = date('n', $meetingDate);
        $nextAppPeriod = $data['next_meeting'];

        // adds select month / year
        $nextMeetingMonth = $meetingMonth + $nextAppPeriod;

        $nextMeetingYear  = $nextMeetingMonth > 12 ? date('Y', $meetingDate) + 1 : date('Y', $meetingDate);
        $nextMeetingMonth = $nextMeetingMonth > 12 ? $nextMeetingMonth - 12 : $nextMeetingMonth;

        // recreate date
        $nextMeetingTmstp = mktime(0, 0, 0,
            $nextMeetingMonth,
            date('j', $meetingDate),
            $nextMeetingYear
        );

        // 7j for notification
        $nextMeetingTmstp -= 7*24*3600;

        $task->setData(array(
            'next_date' => $this->findNextWorkingDay($nextMeetingTmstp)
        ));

        $task->save();

        // notify next node
        $workflow = $task->getWorkflowNode()->getWorkflow();

        return $this->workflows
            ->getNode($workflow, 'appointement')
            ->notify($workflow, $request);
    }
}
