<?php

namespace Extia\Bundle\TaskBundle\Workflow;

use Extia\Bundle\TaskBundle\Model\TaskQuery;
use Extia\Bundle\TaskBundle\Security\Authorization\Voter\TaskVoter;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;
use EasyTask\Bundle\WorkflowBundle\Workflow\Aggregator as BaseAggregator;

use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * override easy task aggregator to render it user responsive
 * @see TaskBundle/Resources/config/services.xml
 */
class Aggregator extends BaseAggregator
{
    protected $securityContext;
    protected $taskVoter;

    /**
     * setup
     * @param SecurityContextInterface $securityContext
     */
    public function setup(SecurityContextInterface $securityContext, TaskVoter $taskVoter)
    {
        $this->securityContext = $securityContext;
        $this->taskVoter       = $taskVoter;
    }

    /**
     * returns only allowed workflows for current user
     * @param  string $level level of required
     * @return array
     */
    public function getAllowed($level)
    {
        $allowedTasks = $this->taskVoter->getTaskCredentials(
            $this->securityContext->getToken()
        );

        if (!isset($allowedTasks[$level])) {
            throw new \InvalidArgumentException(sprintf('Given access level for task is invalid, must be one of "%s"',
                implode('", "', array_values(TaskVoter::$taskAccess))
            ));
        }

        return array_intersect_key(
            $this->all(), array_flip($allowedTasks[$level])
        );
    }

    /**
     * return workflow choices for forms
     * @return array('id' => 'label')
     */
    public function getAsChoices()
    {
        $workflows = $this->getAllowed('write');

        return empty($workflows) ? array() : array_combine(
            array_keys($workflows), array_keys($workflows)
        );
    }

    /**
     * loads current task for given workflow
     * @param  Workflow $workflow
     * @param  Pdo      $pdo
     * @return Task
     */
    public function getCurrentTask(Workflow $workflow, \Pdo $pdo = null)
    {
        return TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->useNodeQuery()
                ->filterByWorkflowId($workflow->getId())
            ->endUse()
            ->joinWithCurrentNodes()
            ->findOne()
        ;
    }

}
