<?php

namespace Extia\Bundle\MissionBundle\Model;

use Extia\Bundle\MissionBundle\Model\om\BaseMission;

use Extia\Bundle\UserBundle\Model\Person;
use Extia\Bundle\UserBundle\Model\MissionOrderQuery;
use Extia\Bundle\UserBundle\Model\ConsultantQuery;
use Extia\Bundle\TaskBundle\Workflow\TaskTargetInterface;

class Mission extends BaseMission implements TaskTargetInterface
{
    /**
     * returns mission full label
     * @param  string $sep
     * @return string
     */
    public function getFullLabel($sep = ' - ')
    {
        return sprintf('%s%s%s',
            $this->getClient()->getTitle(),
            $sep,
            $this->getLabel()
        );
    }

    /**
     * tests if mission is in client office
     * @return boolean
     */
    public function isExternal()
    {
        return 'client' == $this->getType();
    }

    /**
     * tests if mission is intercontract (and on profile without mission)
     * @return boolean
     */
    public function isIntercontract()
    {
        return 'ic' == $this->getType()
            || 'waiting' == $this->getType()
        ;
    }

    // --------------------------------------------------
    // Saving override to calculate fields
    // --------------------------------------------------

    private $oldManager;

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
     * override save to calculate all relative objects
     */
    public function save(\PropelPDO $con = null)
    {
        $return = parent::save($con);

        // fires hooks
        $this->onChangeManager($this->getManager($con), $this->oldManager, $con);

        return $return;
    }

    /**
     * hook fired by save() method, when manager has change
     *
     * @param Person    $newManager
     * @param Person    $oldManager
     * @param PropelPdo $con
     */
    public function onChangeManager(Person $newManager, Person $oldManager = null, \PropelPDO $con = null)
    {
        // change consultants managers

        $consultants = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->useMissionOrderQuery()
                ->filterByCurrent(true)
                ->filterByMissionId($this->getId())
            ->endUse()
            ->find($con)
        ;

        foreach ($consultants as $consultant) {
            $consultant->setManagerId($newManager->getId());
            $consultant->save($con);
        }
    }

    /**
     * Get consultants ids of current mission
     *
     * @return Array
     */
    public function getConsultantsId()
    {
        $consultantsId = MissionOrderQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByMissionId($this->id)
            ->select('ConsultantId')
            ->find();

        return $consultantsId;
    }

    /**
     * Get consultants of current mission
     *
     * @return Array
     */
    public function getConsultants()
    {
        $consultantsId = $this->getConsultantsId()->toArray();
        $consultants = ConsultantQuery::create()->findPKs($consultantsId);

        return $consultants;
    }

    /**
     * @see TaskTargetInterface::getModel()
     */
    public function getModel()
    {
        return 'mission';
    }
}
