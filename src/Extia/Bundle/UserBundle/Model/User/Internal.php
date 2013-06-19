<?php

namespace Extia\Bundle\UserBundle\Model\User;

use Extia\Bundle\UserBundle\Model\User\om\BaseInternal;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Symfony User class
 */
class Internal extends BaseInternal  implements UserInterface
{
    /** salt for admin passwords */
    const ADMIN_PWD_SALT = '';

    /** user roles */
    protected $roles = array();

    /**
     * @return string
     */
    public function getSalt()
    {
        return self::ADMIN_PWD_SALT;
    }

    /**
     * returns current user "Roles", from his AdminProfil
     * @return array
     */
    public function getRoles()
    {
        if (!empty($this->roles)) {
            return $this->roles;
        }

        $this->roles = array('ROLE_USER');

        // loads user group credentials
        foreach ($this->getGroup()->getGroupCredentials() as $groupCredential) {
            $credential = $groupCredential->getCredential();
            $this->roles[] = sprintf('ROLE_%s', strtoupper($credential->getCode()));
        }

        // loads personal credentials
        foreach ($this->getPerson()->getPersonCredentials() as $personCredential) {
            $credential = $personCredential->getCredential();
            $this->roles[] = sprintf('ROLE_%s', strtoupper($credential->getCode()));
        }

        array_unique($this->roles);

        return $this->roles;
    }

    /**
     * proxy method for getLogin() for UserInterface
     * @return string
     */
    public function getUsername()
    {
        return $this->getId();
    }

    /**
     * UserInterface method, unused in this case
     * @{inherited_doc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * equals method for UserInterface
     * @param  UserInterface $user user to compare with
     * @return bool
     */
    public function equals($user)
    {
        return $user instanceof Internal
            && $user->getUsername() === $this->getUsername()
            && $user->getPassword() === $this->getPassword()
            && $user->getSalt()     === $this->getSalt();
    }
}
