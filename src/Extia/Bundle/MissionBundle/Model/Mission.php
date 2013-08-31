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
}
