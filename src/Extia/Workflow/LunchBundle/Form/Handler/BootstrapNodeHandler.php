<?php

namespace Extia\Workflow\LunchBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\om\BaseTaskTargetQuery;
use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Model\TaskTarget;
use Extia\Bundle\TaskBundle\Form\Handler\AbstractNodeHandler;
use Symfony\Component\Form\Form;
use Extia\Bundle\MissionBundle\Model\MissionQuery;
use Extia\Workflow\LunchBundle\Domain\LunchTaskDomain;

/**
 * form handler for bootstrap node
 * @see Extia/Workflow/LunchBundle/Resources/workflows/bootstrap.xml
 */
class BootstrapNodeHandler extends AbstractNodeHandler
{

    protected $lunchTaskDomain;

    public function __construct(LunchTaskDomain $lunchTaskDomain)
    {
        $this->lunchTaskDomain = $lunchTaskDomain;
    }

    /**
     * {@inherit_doc}
     */
    public function resolve(array $data, Task $task, \Pdo $pdo = null)
    {
        // activate before given date for pre-notification
        $task->data()->set('next_meeting_date', $data['next_date']);
        $task->data()->set('notif_date',
            (int) $task->calculateDate($data['next_date'], '-7 days', 'U')
        );

        // updates workflow fields
        $this->updateWorkflow($data, $task, $pdo);

        // calculate task targets
        $mission = MissionQuery::create()->findOneById($data['mission_target_id']);
        $task = $this->lunchTaskDomain->calculateLunchTargets($mission, $task, $pdo);
        $task->save($pdo);

        // notify next node
        return $this->notifyNext('appointement', $task, array(), $pdo);
    }
}
