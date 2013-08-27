<?php

namespace EasyTask\Bundle\WorkflowBundle\Event;

/**
 * Event reference for all workflow bundle events
 */
final class WorkflowEvents
{
    /**
     * Triggered when a workflow is created
     *
     * Give an instance of EasyTask\WorkflowBundle\Event\WorkflowEvent to bound
     * event handler
     *
     * @var string
     * @see EasyTask\WorkflowBundle\Event\WorkflowEvent
     */
    const WF_CREATE = 'workflow.create';

    /**
     * Triggered when a workflow is edit
     *
     * Give an instance of EasyTask\WorkflowBundle\Event\WorkflowEvent to bound
     * event handler
     *
     * @var string
     * @see EasyTask\WorkflowBundle\Event\WorkflowEvent
     */
    const WF_UPDATE = 'workflow.update';

    /**
     * Triggered when a workflow is deleted
     *
     * Give an instance of EasyTask\WorkflowBundle\Event\WorkflowEvent to bound
     * event handler
     *
     * @var string
     * @see EasyTask\WorkflowBundle\Event\WorkflowEvent
     */
    const WF_DELETE = 'workflow.delete';

    /**
     * Triggered when a workflow is boot
     *
     * Give an instance of EasyTask\WorkflowBundle\Event\WorkflowEvent to bound
     * event handler
     *
     * @var string
     * @see EasyTask\WorkflowBundle\Event\WorkflowEvent
     */
    const WF_BOOT = 'workflow.boot';

    /**
     * Triggered when a node is activated (mostly on notify)
     *
     * Give an instance of EasyTask\WorkflowBundle\Event\NodeEvent to bound
     * event handler
     *
     * @var string
     * @see EasyTask\WorkflowBundle\Event\NodeEvent
     */
    const WF_NODE_ACTIVATION = 'workflow.node.activation';

    /**
     * Triggered when a node is shutdown (mostly on notify the next state)
     *
     * Give an instance of EasyTask\WorkflowBundle\Event\NodeEvent to bound
     * event handler
     *
     * @var string
     * @see EasyTask\WorkflowBundle\Event\NodeEvent
     */
    const WF_NODE_SHUTDOWN = 'workflow.node.shutdown';

}
