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
    public function resolve(array $data, Task $task, Request $request, \Pdo $pdo = null)
    {
        // activate before given date for pre-notification
        $task->data()->set('meeting_date', $task->findNextWorkingDay($data['meeting_date']));

        $task->save($pdo);

        // notify next node
        return $this->notifyNext('meeting', $task, array(), $pdo);
    }
}
