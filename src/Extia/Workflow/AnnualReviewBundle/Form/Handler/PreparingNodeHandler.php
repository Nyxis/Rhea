<?php

namespace Extia\Workflow\AnnualReviewBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * form handler for preparing node
 * @see Extia/Workflow/AnnualReviewBundle/Resources/workflows/preparing.xml
 */
class PreparingNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, Request $request)
    {
        $task->addDocument($data['annual_review_doc']);

        $task->data()->set('manager_id', $data['manager_id']);
        $task->data()->set('meeting_date', $task->findNextWorkingDay($data['meeting_date']));

        // // onTaskCreation
        // $task->data()->set('crh_id', $task->getUserAssignedId());
        // $task->setUserAssigned($data['manager_id']);

        $task->save();

        return true;

        return $this->notifyNext('preparing', $task, $request);
    }
}
