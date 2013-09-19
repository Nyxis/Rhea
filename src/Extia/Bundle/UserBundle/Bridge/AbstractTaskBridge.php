<?php

namespace Extia\Bundle\UserBundle\Bridge;

use Extia\Bundle\TaskBundle\Workflow\Aggregator;

/**
 * abstract bridge to workflows bundle
 *
 * @see Extia/Bundles/UserBundle/Resources/config/bridges.xml
 */
abstract class AbstractTaskBridge
{
    protected $workflows;

    /**
     * construct
     * @param Aggregator $workflows
     */
    public function __construct(Aggregator $workflows)
    {
        $this->workflows = $workflows;
    }

    /**
     * Create a task and boot it with given data
     * return created task
     *
     * @param  array $taskData
     * @param  Pdo   $pdo
     * @return Task
     */
    abstract public function createTask(array $taskData);

    /**
     * resolve current node workflow with given data
     *
     * @param Workflow $workflow
     * @param array    $nodeData
     * @param Request  $request
     * @param Pdo      $pdo
     * @return
     */
    protected function resolveCurrentNode(Workflow $workflow, array $nodeData, Request $request, \Pdo $pdo = null)
    {
        $currentTask = $this->workflows->getCurrentTask($workflow, $pdo);

                $task->getNode()->getType()->getHandler()->resolve(array(
                        'user_target_id' => $consultant->getId(),
                        'next_date'   => $task->findNextWorkingDay(
                            (int) $task->calculateDate($consultant->getContractBeginDate(), '+1 year', 'U')
                        ),
                        'workflow' => array(
                            'name'           => $this->translator->trans('annual_review.default_name', array('%user_target%' => $consultant->getLongName())),
                            'description'    => $this->translator->trans('annual_review.default_desc', array('%user_target%' => $consultant->getLongName()))
                        )
                    ), $task, $request, $pdo
                );
    }
}
