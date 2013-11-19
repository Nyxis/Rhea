<?php

namespace Extia\Bundle\UserBundle\Domain;

use Extia\Bundle\UserBundle\Bridge\LunchBridge;
use Extia\Bundle\UserBundle\Model\Consultant;
use Extia\Bundle\MissionBundle\Model\Mission;

class LunchTaskDomain {

    protected $LunchBridge;

    /**
     * construct
     */
    public function __construct(LunchBridge $lunchBridge)
    {
        $this->lunchBridge = $lunchBridge;
    }

    /**
     *  Update lunch task data on mission order creation :
     *      - Add consultant to lunch targets if lunch exist
     *      - Create lunch with consultant as target if lunch doesn't exist
     *
     * @param Consultant $consultant
     * @param Mission    $mission
     * @Param \Pdo       $pdo
     */
    public function updateLunchOnMissionOrderCreation(Consultant $consultant, Mission $mission, \Pdo $pdo = null)
    {
        $task = $this->lunchBridge->getLunchTask($mission, $pdo);

        if (!empty($task))
        {
            $task->addTarget($consultant);
            $task->save();
        }
        else
        {
            $this->lunchBridge->createLunch($consultant, $mission, $pdo);
        }
    }

    /**
     * Close lunch task on mission order deletion
     *      - If $consultant is the last consultant of the lunch, we delete the target
     *      - We close the lunch
     *
     * @param Consultant $consultant
     * @param Mission $mission
     * @param \Pdo $pdo
     */
    public function closeLunchOnMissionOrderDeletion(Consultant $consultant, \Pdo $pdo = null)
    {
        $task = $this->lunchBridge->getLunchTask($consultant, $pdo);

        if (!empty($task))
        {
            if ($task->getTaskTargets()->count() == 2)
            {
                $this->lunchBridge->closeLunch($task, $pdo);
            }

            $task->removeTarget($consultant);
            $task->save($pdo);
        }
    }

}
