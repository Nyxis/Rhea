<?php

namespace Extia\Workflow\AnnualReviewBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * form handler for initiation node
 * @see Extia/Workflow/AnnualReviewBundle/Resources/workflows/initiation.xml
 */
class InitiationNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, Request $request)
    {
        $task->setUserTargetId($data['user_target_id']);

        $task->setActivationDate(strtotime(date('Y-m-d')));
        $task->defineCompletionDate('+1 day');

        // activate before given date for pre-notification
        $task->data()->set('meeting_date', $task->findNextWorkingDay($data['next_date']));
        $task->data()->set('notif_date', $task->findNextWorkingDay(
            (int) $task->calculateDate($data['next_date'], '-21 days', 'U')
        ));

        // updates workflow fields
        $this->updateWorkflow($data, $task);

        $task->save();

        return true;

        return $this->notifyNext('appointement', $task, $request);
    }
}
