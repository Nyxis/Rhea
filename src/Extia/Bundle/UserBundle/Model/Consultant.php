<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BaseConsultant;

class Consultant extends BaseConsultant
{
    protected $currentMissionOrder = false;

    const STATUS_PLACED    = 'placed';
    const STATUS_IC        = 'ic';
    const STATUS_RECRUITED = 'recruited';
    const STATUS_RESIGNED  = 'resigned';

    /**
     * returns consultant status
     * @return string
     */
    public function getStatus()
    {
        $resignationId = $this->getResignationId();
        if (!empty($resignationId)) {
            return self::STATUS_RESIGNED;
        }

        $currentMission = $this->getCurrentMission();

        if ($currentMission->getType() == 'ic') {
            return self::STATUS_IC;
        }

        if ($currentMission->getType() == 'waiting') {
            return self::STATUS_RECRUITED;
        }

        return self::STATUS_PLACED;
    }

    /**
     * selects and returns current mission
     *
     * @param  \Pdo    $con option db connection
     * @return Mission
     */
    public function getCurrentMission(\Pdo $con = null)
    {
        $currentOrder = $this->getCurrentMissionOrder($con);

        return $currentOrder ? $currentOrder->getMission() : null;
    }

    /**
     * select and return current mission order
     *
     * @param  \Pdo         $con
     * @return MissionOrder
     */
    public function getCurrentMissionOrder(\Pdo $con = null)
    {
        if ($this->currentMissionOrder !== false) {
            return $this->currentMissionOrder;
        }

        $this->currentMissionOrder = null;

        $missionsOrders = $this->getMissionOrders(
            MissionOrderQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterByCurrent(true)
                ->orderByBeginDate(\Criteria::DESC)
                ->joinWith('Mission')
                ->joinWith('Mission.Client')
                ->joinWith('Mission.Manager')
        );

        if (!$missionsOrders->isEmpty()) { // no more selects
            $this->currentMissionOrder = $missionsOrders->getFirst();
        }

        return $this->currentMissionOrder;
    }

    // --------------------------------------------------
    // Saving override to calculate fields
    // --------------------------------------------------

    private $oldManager;
    private $oldCrh;

    /**
     * override setter to memento manager before modification
     */
    public function setManagerId($managerId)
    {
        if ($managerId !== $this->getManagerId()) {
            $this->oldManager = $this->getManager();
        }

        return parent::setManagerId($managerId);
    }

    /**
     * override setter to memento crh before modification
     */
    public function setCrhId($crhId)
    {
        if ($crhId !== $this->getCrhId()) {
            $this->oldCrh = $this->getCrh();
        }

        return parent::setCrhId($crhId);
    }

    /**
     * override save to calculate all relative objects
     */
    public function save(\PropelPDO $con = null)
    {
        $return = parent::save($con);

        // fires hooks
        $this->onChangeManager($this->getManager($con), $this->oldManager, $con);
        $this->onChangeCrh($this->getCrh($con), $this->oldCrh, $con);

        return $return;
    }

    /**
     * hook fired by save() method, when manager has change
     *
     * @param Internal  $newManager
     * @param Internal  $oldManager
     * @param PropelPdo $con
     */
    public function onChangeManager(Internal $newManager, Internal $oldManager = null, \PropelPDO $con = null)
    {
        // consultant switch into manager's agency
        // and old and new manager have to updates number of clt

        $this->setAgencyId($newManager->getAgencyId());
        $newManager->calculateNbConsultants();
        parent::save($con); // fires cascade saving

        if (!empty($oldManager)) {
            $oldManager->calculateNbConsultants()->save($con);
        }
    }

    /**
     * hook fired by save() method, when crh has change
     *
     * @param Internal  $newCrh
     * @param Internal  $oldCrh
     * @param PropelPdo $con
     */
    public function onChangeCrh(Internal $newCrh, Internal $oldCrh = null, \PropelPDO $con = null)
    {
        $newCrh->calculateNbConsultants()->save($con);

        if (!empty($oldCrh)) {
            $oldCrh->calculateNbConsultants()->save($con);
        }
    }
}
