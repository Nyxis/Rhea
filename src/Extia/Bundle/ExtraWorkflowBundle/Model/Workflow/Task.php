<?php

namespace Extia\Bundle\ExtraWorkflowBundle\Model\Workflow;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\om\BaseTask;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Task class
 */
class Task extends BaseTask
{
    protected $dataBag;

    /**
     * access data bag
     * @return ParameterBag
     */
    public function data()
    {
        // triggers loading
        $this->getData();

        return $this->dataBag;
    }

    /**
     * proxy used to lazy data decode and bag creation
     * @return ParameterBag
     */
    public function getData()
    {
        if ($this->dataBag instanceof ParameterBag) {
            return $this->dataBag->all();
        }

        $data = parent::getData();

        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        if (empty($data)) {
            $data = array();
        }

        $this->dataBag = new ParameterBag($data);

        return $this->dataBag->all();
    }

    /**
     * replace all data with given once
     * @param array $data
     */
    public function setData($data)
    {
        $this->dataBag->replace((array) $data);

        return parent::setData(json_encode($this->dataBag->all()));
    }

    /**
     * proxy on save to always insert data
     * @param  PropelPDO $pdo
     * @return bool
     */
    public function save(\PropelPDO $pdo = null)
    {
        $this->setData(
            $this->getData()
        );

        return parent::save($pdo);
    }
}
