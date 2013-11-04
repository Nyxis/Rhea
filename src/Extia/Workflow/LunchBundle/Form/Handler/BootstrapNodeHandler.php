<?php

namespace Extia\Workflow\LunchBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\om\BaseTaskTargetQuery;
use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Model\TaskTarget;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;

use Symfony\Component\Form\Form;
use Extia\Bundle\UserBundle\Model\MissionOrderQuery;

/**
 * form handler for bootstrap node
 * @see Extia/Workflow/LunchBundle/Resources/workflows/bootstrap.xml
 */
class BootstrapNodeHandler extends AbstractNodeHandler
{
    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        $task->setActivationDate(strtotime(date('Y-m-d')));
        $task->defineCompletionDate('+1 day');

        // activate before given date for pre-notification
        $task->data()->set('next_meeting_date', $data['next_date']);
        $task->data()->set('notif_date',
            (int) $task->calculateDate($data['next_date'], '-7 days', 'U')
        );

        // updates workflow fields
        $this->updateWorkflow($data, $task, $pdo);

        $consultants = MissionOrderQuery::create()
            ->filterByMissionId($data['mission_target_id'])
            ->select('ConsultantId')
            ->find();

        // Insert of task targets
        $task->addTarget($this->loadMission($data['mission_target_id'], $pdo));
        foreach ($consultants as $consultant_id)
        {
            $task->addTarget($this->loadConsultant($consultant_id, $pdo));
        }
        $task->save($pdo);

        // notify next node
        return $this->notifyNext('appointement', $task, array(), $pdo);
    }
}
