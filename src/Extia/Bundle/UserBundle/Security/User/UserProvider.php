<?php

namespace Extia\Bundle\UserBundle\Security\User;

use Extia\Bundle\UserBundle\Model\User\InternalQuery;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * class which loads users from database
 *
 * @see Extia/Bundle/UserBundle/Resources/config/services.xml
 */
class UserProvider implements UserProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        try {
            $user = InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->joinWith('Person')

                // id or email instead of context (authentication or refresh)
                ->_if(is_integer($username))
                    ->filterById($username)
                ->_else()
                    ->filterByEmail($username)
                ->_endif()

                // loads group
                ->joinWith('Group')
                ->joinWith('Group.GroupI18n')
                ->joinWith('Group.GroupCredential', \Criteria::LEFT_JOIN)
                ->joinWith('Group.GroupCredential.Credential', \Criteria::LEFT_JOIN)
                ->joinWith('Group.GroupCredential.Credential.CredentialI18n', \Criteria::LEFT_JOIN)

                ->findOne();
            ;

            return $user;

        } catch (\Exception $e) {
            throw $e;
        }

        throw new UsernameNotFoundException(sprintf('User "%s" not found or cannot be authenticated.', $username));
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Extia\Bundle\UserBundle\Model\User\Internal';
    }
}
