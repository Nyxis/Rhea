<?php

namespace Extia\Workflow\CrhMonitoringBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * form handler for bootstrap node
 * @see Extia/Workflow/CrhMonitoringBundle/Resources/workflows/bootstrap.xml
 */
class BootstrapNodeHandler extends AbstractNodeHandler
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

        $task->setUserTargetId($data['user_target_id']);

        $task->setActivationDate(strtotime(date('Y-m-d')));
        $task->defineCompletionDate('+1 day');

        // activate before given date for pre-notification
        $task->data()->set('meeting_date', $task->findNextWorkingDay($data['next_date']));
        $task->data()->set('notif_date', $task->findNextWorkingDay(
            (int) $task->calculateDate($data['next_date'], '-7 days', 'U')
        ));

        // updates workflow fields
        $this->updateWorkflow($data, $task);

        $task->save();

        // notify next node
        $workflow = $task->getNode()->getWorkflow();

        return $this->workflows
            ->getNode($workflow, 'appointement')
            ->notify($workflow, $request);
    }
}
