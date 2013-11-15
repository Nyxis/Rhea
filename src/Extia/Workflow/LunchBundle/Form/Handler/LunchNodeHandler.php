<?php

namespace Extia\Workflow\LunchBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;

/**
 * form handler for lunch node
 * @see Extia/Workflow/LunchBundle/Resources/workflows/lunch.xml
 */
class LunchNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        $nextLunchTmstp = $task->calculateDate($task->getActivationDate(), '+2 months', 'U');

        $task->data()->set('next_meeting_date', $task->findNextWorkingDay($nextLunchTmstp));
        $task->data()->set('notif_date', $task->findNextWorkingDay(
            (int) $task->calculateDate($nextLunchTmstp, '-7 days', 'U'))
        );

        $task->save($pdo);

        // notify next node
        return $this->notifyNext('appointement', $task, array(), $pdo);
    }
}
