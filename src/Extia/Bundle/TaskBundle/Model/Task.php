<?php

namespace Extia\Bundle\TaskBundle\Model;

use Extia\Bundle\TaskBundle\Model\om\BaseTask;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Task class
 */
class Task extends BaseTask
{
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

    // ---------------------------------------------------------
    // Data management
    // ---------------------------------------------------------

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

    // ---------------------------------------------------------
    // States
    // ---------------------------------------------------------
    const STATE_PLANED  = 'planed';
    const STATE_HANDLED = 'handled';
    const STATE_PAST    = 'past';

    // ---------------------------------------------------------
    // Basic tests
    // ---------------------------------------------------------

    /**
     * @return boolean
     */
    public function isPlanedToday()
    {
        return $this->getActivationDate('d/m/Y') == date('d/m/Y');
    }

    /**
     * @return boolean
     */
    public function isPlanedTomorrow()
    {
        $diffSeconds = strtotime($this->getActivationDate('Y-m-d')) - strtotime(date('Y-m-d'));

        return $diffSeconds >= 3600*24 && $diffSeconds <= 3600*48;
    }
}
