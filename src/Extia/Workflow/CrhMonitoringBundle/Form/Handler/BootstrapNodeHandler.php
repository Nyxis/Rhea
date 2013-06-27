<?php

namespace Extia\Workflow\CrhMonitoringBundle\Form\Handler;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\Task;
use Extia\Bundle\ExtraWorkflowBundle\Form\Handler\AbstractNodeHandler;

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

        $task->setData(array(
            // activate before given date for pre-notification
            'next_date' => $this->findNextWorkingDay($data['next_date']-(7*24*3600))
        ));

        $task->save();

        // notify next node
        $workflow = $task->getWorkflowNode()->getWorkflow();

        return $this->workflows
            ->getNode($workflow, 'appointement')
            ->notify($workflow, $request);
    }
}
