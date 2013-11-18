<?php

namespace Extia\Workflow\AnnualReviewBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;

/**
 * form handler for preparing node
 * @see Extia/Workflow/AnnualReviewBundle/Resources/workflows/preparing.xml
 */
class PreparingNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        $task->addDocument($data['annual_review_doc']);

        $task->data()->set('manager_id', $data['manager_id']);
        $task->data()->set('meeting_date',
            $this->temporalTools->findNextWorkingDay($data['meeting_date'])
        );

        $task->save($pdo);

        return $this->notifyNext('annual_meeting', $task, array(), $pdo);
    }
}
