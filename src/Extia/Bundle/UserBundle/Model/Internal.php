<?php

namespace Extia\Bundle\UserBundle\Model;

use Extia\Bundle\UserBundle\Model\om\BaseInternal;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

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
     * builds and return trigram - firstname lastname
     * @return string
     */
    public function getTryptedName($sep = ' - ')
    {
        return sprintf('%s%s%s',
            $this->getTrigram(), $sep, $this->getLongname()
        );
    }

    /**
     * tests if current use is active
     * @return boolean
     */
    public function isActive()
    {
        return $this->getResignation() === null;
    }

    /**
     * calculate and returns date interval between now and contract begin date
     * @return DateInterval
     */
    public function getSeniority()
    {
        $begin = $this->getContractBeginDate();
        $ref   = $this->isActive() ? new \DateTime() : $this->getResignation()->getLeaveAt();

        return $begin->diff($ref);
    }

    /**
     * returns team user ids
     * @return array
     */
    public function getTeamIds()
    {
        return $this->getBranch(
            InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->select(array('Id'))
                ->filterById($this->getId(), \Criteria::NOT_EQUAL)
                ->filterByDescendantClass(null, \Criteria::ISNULL) // only internals
        );
    }

    /**
     * returns internal consultants (related if crh, throught mission if manager)
     * @return array
     */
    public function getConsultantsIds()
    {
        $consultantsId = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->select(array('Id'))
            ->filterByInternalReferer($this)
            ->find();

        return $consultantsId;
    }

    /**
     * count consultants
     * doesn't use calculated field, but used for calculate it
     *
     * @return int
     */
    public function countConsultants()
    {
        return count($this->getConsultantsIds());
    }

    /**
     * calculates internal nb related consultants
     *
     * @return Internal
     */
    public function calculateNbConsultants()
    {
        $this->setNbConsultants(
            $this->countConsultants()
        );

        return $this;
    }

    /**
     * count active tasks
     * @return int
     */
    public function countActiveTasks()
    {
        return $this->hasVirtualColumn('nbActiveTasks') ?
            $this->getVirtualColumn('nbActiveTasks') :
            $this->getActiveTasks()->count()
        ;
    }

    /**
     * returns internal active assigned tasks
     * @return PropelCollection|null
     */
    public function getActiveTasks()
    {
        return TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByAssignedTo($this->getId())
            ->useNodeQuery()
                ->filterByCurrent(true)
            ->endUse()
            ->find();
    }
}
