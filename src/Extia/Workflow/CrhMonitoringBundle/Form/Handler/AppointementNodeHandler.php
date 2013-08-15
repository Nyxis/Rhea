<?php

namespace Extia\Workflow\CrhMonitoringBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * form handler for appointement node
 * @see Extia/Workflow/CrhMonitoringBundle/Resources/workflows/appointement.xml
 */
class AppointementNodeHandler extends AbstractNodeHandler
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

        // activate before given date for pre-notification
        $task->data()->set('meeting_date', $task->findNextWorkingDay($data['meeting_date']));

        $task->save();

        // notify next node
        $workflow = $task->getNode()->getWorkflow();

        return $this->workflows
            ->getNode($workflow, 'meeting')
            ->notify($workflow, $request);
    }
}
