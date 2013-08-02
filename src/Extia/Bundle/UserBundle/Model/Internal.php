<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BaseInternal;

use Extia\Bundle\GroupBundle\Model\GroupQuery;
use Extia\Bundle\GroupBundle\Model\CredentialQuery;

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
        $group = GroupQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterById($this->getGroupId())
            ->joinWith('GroupCredential')
            ->find();

        foreach ($group->getFirst()->getGroupCredentials() as $groupCredential) {
            $credential = $groupCredential->getCredential();
            $this->roles[] = sprintf('ROLE_%s', strtoupper($credential->getCode()));
        }

        // loads personal credentials
        $credentials = CredentialQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->usePersonCredentialQuery()
                ->filterByPersonId($this->getId())
            ->endUse()
            ->find();

        foreach ($credentials as $credential) {
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

    /**
     * returns team user ids
     * @return array
     */
    public function getTeamIds()
    {
        return $this->getDescendants(
            InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->select(array('Id'))
        );
    }
}
