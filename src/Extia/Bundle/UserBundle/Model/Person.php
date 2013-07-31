<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BasePerson;

class Person extends BasePerson
{
    /**
     * return consultant concatened name
     * @param  string $sep optionnal separator between firstname and lastname
     * @return string
     */
    public function getLongName($sep = ' ')
    {
        return sprintf('%s%s%s',
            $this->getFirstname(), $sep, $this->getLastname()
        );
    }
}