<?php

namespace Extia\Bundle\TaskBundle\Model;

use Extia\Bundle\TaskBundle\Model\om\BaseTask;
use Extia\Bundle\TaskBundle\Workflow\TaskTargetInterface;

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
        foreach ($this->getTargetedPersons() as $person) {
            $personTaskDocument = new PersonTaskDocument();
            $personTaskDocument->setPerson($person);
            $personTaskDocument->setDocument($document);
            $personTaskDocument->setTask($this);
        }

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
        if ($this->getStatus() == self::STATE_PLANED) {
            return 0; // no retard if no activated
        }

        $completionDate = strtotime($this->getCompletionDate('Y-m-d'));
        if (empty($completionDate)) {
            return 0; // no completion date, no retard
        }

        $date = new \DateTime();
        $date->setTimestamp($this->isCompleted() ? strtotime($this->getCompletedAt('Y-m-d')) : strtotime(date('Y-m-d')));

        $diff = $date->diff(
            // retard relative to day before deadline
            \DateTime::createFromFormat('U', $completionDate - 3600*24)
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
    // Proxy to targets
    // ---------------------------------------------------------

    protected $targets = array();

    /**
     * loads task targets
     *
     * @param  Pdo   $pdo opt pdo connection
     * @return array
     */
    protected function loadTargets(\Pdo $pdo = null)
    {
        if (!empty($this->targets) && empty($pdo)) {
            return $this->targets;
        }

        $this->targets = array();
        foreach ($this->getTaskTargets() as $taskTarget) {
            $this->targets[] = $taskTarget->getTarget($pdo);
        }

        return $this->targets;
    }

    /**
     * retrieve all persons targeted by this task
     *
     * @param  string $model filter to only return targets of given model
     * @param  Pdo    $pdo   opt pdo connection
     * @return array
     */
    public function getTargets($model = null, \Pdo $pdo = null)
    {
        $targets = $this->loadTargets($pdo);

        if (empty($model)) {
            return $targets;
        }

        return array_filter($targets, function(TaskTargetInterface $target) use ($model) {
            return $target->getModel() == $model;
        });
    }

    /**
     * defines task targets
     * @param array $targets
     */
    public function setTargets($targets)
    {
        $this->targets = $targets;

        return $this;
    }

    /**
     * retrive first target for this task
     *
     * @param  string              $model
     * @param  Pdo                 $pdo   pdo connection
     * @return TaskTargetInterface
     */
    public function getTarget($model, \Pdo $pdo = null)
    {
        $targets = $this->getTargets($model, $pdo);

        return empty($targets) ? $targets : array_shift($targets);
    }

    /**
     * adds a new task target for this task
     * @param  TaskTargetInterface $taskTarget object to target
     * @return Task
     */
    public function addTarget(TaskTargetInterface $targetObject)
    {
        $taskTarget = new TaskTarget();
        $taskTarget->setTargetModel(get_class($targetObject));
        $taskTarget->setTargetId($targetObject->getPrimaryKey());
        $taskTarget->setTask($this);

        $this->targets[] = $targetObject;

        return $this;
    }

    /**
     * adds a new task target for this task
     * @param  TaskTargetInterface $taskTarget object to target
     * @return Task
     */
    public function removeTarget(TaskTargetInterface $targetObject)
    {
        foreach ($this->getTarget as $key => $target) {
            if (get_class($targetObject) == get_class($target) && $targetObject->getPrimaryKey() == $target->getPrimaryKey()) {
                unset($this->targets[$key]);
                break;
            }
        }

        return $this;
    }

    /**
     * migrate given task target to this one
     *
     * @param  Task $task task to migrate
     * @return Task this task
     */
    public function migrateTargets(Task $task)
    {
        foreach ($task->getTaskTargets() as $taskTarget) {
            $this->addTaskTarget(
                $taskTarget->copy()
            );
        }
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
        $activationDate = strtotime($this->getActivationDate('Y-m-d'));
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
     *
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
}
