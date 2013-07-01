<?php

namespace Extia\Bundle\UserBundle\Model\User;

use Extia\Bundle\UserBundle\Model\User\om\BasePerson;

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
