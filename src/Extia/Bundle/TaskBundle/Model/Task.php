<?php

namespace Extia\Bundle\TaskBundle\Model;

use Extia\Bundle\TaskBundle\Model\om\BaseTask;

use Extia\Bundle\UserBundle\Model\PersonTaskDocument;
use Extia\Bundle\DocumentBundle\Model\Document;

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

        return strtotime($this->getCompletionDate('Y-m-d')) < time() ? self::STATE_PAST : self::STATE_PLANED;
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
    // Documents
    // ---------------------------------------------------------

    /**
     * adds a document to task
     * @param  Document $document
     * @return Task
     */
    public function addDocument(Document $document)
    {
        $personTaskDocument = new PersonTaskDocument();
        $personTaskDocument->setPersonId($this->getUserTargetId());
        $personTaskDocument->setDocument($document);
        $personTaskDocument->setTask($this);

        return $this;
    }


    // ---------------------------------------------------------
    // Basic accessors
    // ---------------------------------------------------------

    /**
     * calculate and returns number of days of retard
     * @return int
     */
    public function getPastDays()
    {
        $date = new \DateTime();
        $date->setTimestamp($this->isCompleted() ? strtotime($this->getCompletedAt('Y-m-d')) : strtotime(date('Y-m-d')));

        $diff = $date->diff(
            \DateTime::createFromFormat('U', strtotime($this->getCompletionDate('Y-m-d')))
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


    // ---------------------------------------------------------
    // Temporal tools
    // ---------------------------------------------------------

    /**
     * alias to setCompletionDate to use with period from activation date
     *
     * @param string $period
     */
    public function defineCompletionDate($period)
    {
        $activationDate = $this->getActivationDate();
        if (empty($activationDate)) {
            return $this;
        }

        $this->setCompletionDate(
            $this->findNextWorkingDay(
                $this->calculateDate($activationDate, $period, 'U')
            )
        );

        return $this;
    }

    /**
     * calculate a date with period
     * @param  DateTime|string $date
     * @param  string          $period period
     * @param  string          $format optionnal output format for datetime object
     * @return DateTime
     */
    public function calculateDate($date, $period, $output = null)
    {
        if (is_numeric($date)) {
            $date = \DateTime::createFromFormat('U', $date);
        }

        $newDate = $date->add(\DateInterval::createFromDateString($period));

        return $output ? $newDate->format($output) : $newDate;
    }

    /**
     * find next working day
     * @param  timestamp $timestamp
     * @return timestamp
     */
    public function findNextWorkingDay($timestamp)
    {
        $workingDays = range(1,5);
        $offDays     = range(6,7);

        while (in_array(date('N', $timestamp), $offDays)) {
            $timestamp += 3600*24;
        }

        return $timestamp;
    }

    /**
     * adds $nbMonths to given date
     * @param  timestamp $date
     * @param  int       $nbMonths
     * @return timestamp
     */
    public function addMonths($date, $nbMonths)
    {
        $dateMonth  = date('n', $date);

        // adds select month / year
        $nextDateMonth = $dateMonth + $nbMonths;

        $nextDateYear  = $nextDateMonth > 12 ? date('Y', $date) + floor($nextDateMonth/12) : date('Y', $date);
        $nextDateMonth = $nextDateMonth > 12 ? $nextDateMonth % 12 : $nextDateMonth;

        // recreate date
        $nextDateTmstp = mktime(
            0, 0, 0, // on midnight
            $nextDateMonth, date('j', $date), $nextDateYear
        );

        return $nextDateTmstp;
    }
}
