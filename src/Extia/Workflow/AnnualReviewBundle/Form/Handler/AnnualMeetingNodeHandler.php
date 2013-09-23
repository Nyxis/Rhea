<?php

namespace Extia\Workflow\AnnualReviewBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;

/**
 * form handler for preparing node
 * @see Extia/Workflow/AnnualReviewBundle/Resources/workflows/preparing.xml
 */
class AnnualMeetingNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        $task->addDocument($data['annual_review_doc']);

        $yearActivation = $task->getActivationDate('Y');
        $monthContract  = $task->getUserTarget()->getInternal()->getContractBeginDate('m');
        $dayContract    = $task->getUserTarget()->getInternal()->getContractBeginDate('d');

        $nextTimestamp = mktime(0, 0, 0, $monthContract, $dayContract, $yearActivation);

        $task->data()->set('meeting_date', $task->findNextWorkingDay(
            $task->calculateDate($nextTimestamp, '+1 year', 'U')
        ));

        $task->save($pdo);

        return $this->notifyNext('preparing', $task, array(), $pdo);
    }
}
