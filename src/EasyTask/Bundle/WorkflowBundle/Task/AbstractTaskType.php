<?php

namespace EasyTask\Bundle\WorkflowBundle\Task;

/**
 * abstract task type, defines Task type service structure
 */
abstract class AbstractTaskType extends ParameterBag
{
    /**
     * abstract method which have to returns generated view
     */
    abstract public function getCurrentStateView();

}
