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
    // Task states
    // ---------------------------------------------------------
    const STATE_PLANED  = 'planed';
    const STATE_HANDLED = 'handled';
    const STATE_PAST    = 'past';

    /**
     * returns task status
     * @return string
     */
    public function getStatus()
    {
        if ($this->isCompleted()) {
            return self::STATE_HANDLED;
        }

        if ($this->isPlanedToday()) {
            return self::STATE_PLANED;
        }

        return strtotime($this->getActivationDate('Y-m-d')) + 24 * 3600 > time() ?
            self::STATE_PLANED : self::STATE_PAST;
    }

    // ---------------------------------------------------------
    // Workflow states
    // ---------------------------------------------------------
    const WF_STATE_RUNNING  = 'running';
    const WF_STATE_CLOSED   = 'closed';
    const WF_STATE_RETARDED = 'retarded';

    /**
     * calculate and returns workflow state
     * @notes defined here because workflow class doesn't have information
     * about retard and so on
     *
     * @return string
     */
    public function getWorkflowStatus()
    {
        if ($this->getNode()->getEnded()) {
            return self::WF_STATE_CLOSED;
        }

        return $this->getPastDays() ? self::WF_STATE_RETARDED : self::WF_STATE_RUNNING;
    }


    // ---------------------------------------------------------
    // Basic accessors
    // ---------------------------------------------------------

    /**
     * return limit date to complete this task
     * @param  string          $format optional date format, if given, returns formated date isntead of Datetime
     * @return DateTime|string
     */
    public function getCompletionLimitDate($format = null)
    {
        $date = new \DateTime();
        $date->setTimestamp(strtotime($this->getActivationDate('Y-m-d')) + 3600 * 24);

        return is_string($format) ? $date->format($format) : $date;
    }

    /**
     * calculate and returns number of days of retard
     * @return int
     */
    public function getPastDays()
    {
        $date = new \DateTime();
        $date->setTimestamp($this->isCompleted() ? strtotime($this->getCompletedAt('Y-m-d')) : strtotime(date('Y-m-d')));

        $diff = $date->diff(
            \DateTime::createFromFormat('U', strtotime($this->getActivationDate('Y-m-d')))
        );

        return $diff->invert === 1 ? $diff->days : 0;
    }

    /**
     * @return boolean
     */
    public function isCompleted()
    {
        return !is_null($this->getNode()->getCompletedAt());
    }

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
