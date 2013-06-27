<?php

namespace Extia\Bundle\ExtraWorkflowBundle\Model\Workflow;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\om\BaseTask;

/**
 * Task class
 */
class Task extends BaseTask
{
    public function getData()
    {
        $data = parent::getData();

        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        if (empty($data)) {
            $data = array();
        }

        return $data;
    }

    public function setData($data)
    {
        if (empty($data)) {
            $data = array();
        }

        if (is_array($data)) {
            $data = json_encode($data);
        }

        return parent::setData($data);
    }
}
