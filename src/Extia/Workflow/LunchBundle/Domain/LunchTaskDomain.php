<?php

namespace Extia\Workflow\LunchBundle\Domain;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\MissionBundle\Model\Mission;
use Extia\Bundle\MissionBundle\Model\MissionQuery;

/**
 * LunchTask domain, repository of lunch task logic
 *
 * @see ExtiaUserBundle/Resources/config/domains.xml
 */
class LunchTaskDomain {

    /**
     * Get mission of specific lunch, a lunch is composed of
     * one mission and n consultants as targets
     *
     * @param PropelObjectCollection $taskTargets
     * @return Mission
     */
    public function getLunchTargetedMission(\PropelObjectCollection $taskTargets)
    {
        $mission = MissionQuery::create();
        $missionClass = $mission->getModelName();

        foreach ($taskTargets as $taskTarget)
        {
            if ($taskTarget->getTargetModel() == $missionClass)
            {
                $missionTargetId = $taskTarget->getTargetId();
            }
        }
        $mission = $mission->findOneById($missionTargetId);

        return $mission;
    }

    /**
     * calculate lunch targets, one mission and n consultants
     *
     * @param Mission $mission mission of lunch task
     * @param Task $task
     * @return Task
     */
    public function calculateLunchTargets(Mission $mission, Task $task)
    {
        $consultants = $mission->getConsultants();

        // Insert of task targets
        $task->addTarget($mission);
        foreach ($consultants as $consultant)
        {
            $task->addTarget($consultant);
        }
        return $task;
    }

}