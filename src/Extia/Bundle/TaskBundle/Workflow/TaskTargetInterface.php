<?php

namespace Extia\Bundle\TaskBundle\Workflow;

/**
 * interface to implements on task target models
 */
interface TaskTargetInterface extends \Persistent
{
    /**
     * has to return model php name
     *
     * @return string
     */
    public function getModel();
}
