<?php

namespace Extia\Workflow\AnnualReviewBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;

/**
 * form handler for initiation node
 * @see Extia/Workflow/AnnualReviewBundle/Resources/workflows/initiation.xml
 */
class InitiationNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        $task->addTarget($this->loadConsultant($data['user_target_id']), $pdo);

        $task->setActivationDate(strtotime(date('Y-m-d')));
        $task->defineCompletionDate('+1 day');

        $task->data()->set('meeting_date', $task->findNextWorkingDay($data['next_date']));

        // updates workflow fields
        $this->updateWorkflow($data, $task);

        $task->save($pdo);

        return $this->notifyNext('preparing', $task, array(), $pdo);
    }
}
