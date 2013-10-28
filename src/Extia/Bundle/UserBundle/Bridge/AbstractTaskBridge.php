<?php

namespace Extia\Bundle\UserBundle\Bridge;

use Extia\Bundle\UserBundle\Model\Consultant;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Model\TaskQuery;
use Extia\Bundle\TaskBundle\Workflow\Aggregator;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * abstract bridge to workflows bundle
 *
 * @see Extia/Bundles/UserBundle/Resources/config/bridges.xml
 */
abstract class AbstractTaskBridge
{
    protected $workflows;
    protected $translator;

    /**
     * construct
     * @param Aggregator $workflows
     */
    public function __construct(Aggregator $workflows, TranslatorInterface $translator)
    {
        $this->workflows  = $workflows;
        $this->translator = $translator;
    }

    /**
     * has to return bridged workflow name
     *
     * @return string
     */
    abstract protected function getBridgedWorkflow();

    /**
     * Create a task and boot it with given data
     * return created task
     *
     * @param  array $taskData
     * @param  Pdo   $pdo
     * @return Task
     */
    public function createWorkflow(array $taskData = array(), \Pdo $pdo = null)
    {
        $workflow = $this->workflows->create(
            $this->getBridgedWorkflow()
        );

        $this->workflows->boot(
            $workflow, $taskData, $pdo
        );

        return $this->workflows->getCurrentTask($workflow, $pdo);
    }

    /**
     * resolve given task
     *
     * @param Task  $task
     * @param array $nodeData
     * @param Pdo   $pdo
     * @return
     */
    protected function resolveNode(Task $task, array $nodeData = array(), \Pdo $pdo = null)
    {
        if (!$task->getNode()->getCurrent()
                || $task->getNode()->getCompletedAt() !== null
                || $task->getNode()->getEnded()
            ) {
            throw new \InvlidArgumentException('Cannot resolved as closed or a completed workflow node.');
        }

        return $task->getNode()->getType()->getHandler()->resolve(
            $nodeData, $task, $pdo
        );
    }

    /**
     * close all mission monitoring for given consultant
     *
     * @param  Consultant $consultant
     * @param  \Pdo       $pdo
     * @return int        number of closed workflows
     */
    public function closeMonitorings(Consultant $consultant, \Pdo $pdo = null)
    {
        $tasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))

            ->useNodeQuery()
                ->filterByCurrent(true)
                ->useWorkflowQuery()
                    ->filterByType($this->getBridgedWorkflow())
                ->endUse()
            ->endUse()
            ->filterByTarget($consultant)

            ->joinWith('Node')
            ->joinWith('Node.Workflow')

            ->find($pdo)
        ;

        foreach ($tasks as $task) {
            $node = $task->getNode();
            $node->setEnded(true);
            $node->setCurrent(false);
            $node->save($pdo);
        }

        return $tasks->count();
    }
}
