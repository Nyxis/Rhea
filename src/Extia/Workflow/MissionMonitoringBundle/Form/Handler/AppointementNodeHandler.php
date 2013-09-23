<?php

namespace Extia\Workflow\MissionMonitoringBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;

/**
 * form handler for appointement node
 * @see Extia/Workflow/MissionMonitoringBundle/Resources/workflows/appointement.xml
 */
class AppointementNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        $task->data()->set('meeting_date', $data['meeting_date']);
        $task->data()->set('contact_name', $data['contact_name']);
        $task->data()->set('contact_email', $data['contact_email']);
        $task->data()->set('contact_tel', $data['contact_tel']);

        $task->save($pdo);

        // notify next node
        return $this->notifyNext('meeting', $task, array(), $pdo);
    }
}
