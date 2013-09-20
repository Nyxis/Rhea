<?php

namespace Extia\Bundle\MissionBundle\Model;

use Extia\Bundle\MissionBundle\Model\om\BaseMission;

class Mission extends BaseMission
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
}
