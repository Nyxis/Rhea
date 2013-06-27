<?php

namespace Extia\Workflow\CrhMonitoringBundle\Form\Handler;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\Task;
use Extia\Bundle\ExtraWorkflowBundle\Form\Handler\AbstractNodeHandler;

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

        $task->setData(array(
            // activate before given date for pre-notification
            'meeting_date' => $this->findNextWorkingDay($data['meeting_date'])
        ));

        $task->save();

        // notify next node
        $workflow = $task->getWorkflowNode()->getWorkflow();

        return $this->workflows
            ->getNode($workflow, 'meeting')
            ->notify($workflow, $request);
    }
}
