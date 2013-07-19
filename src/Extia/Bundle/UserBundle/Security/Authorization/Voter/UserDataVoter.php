<?php

namespace Extia\Bundle\UserBundle\Security\Authorization\Voter;

use Extia\Bundle\UserBundle\Model\Internal;
use Extia\Bundle\UserBundle\Model\Consultant;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Voter which grant access of a person data
 * @see Extia/Bundle/UserBundle/Resources/config/services.xml
 */
class UserDataVoter implements VoterInterface
{
    public function supportsAttribute($attribute)
    {
        return 'USER_DATA' === $attribute;
    }

    public function supportsClass($class)
    {
        return $class instanceof Internal;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute) && $this->supportsClass($object)) {

                // admin can access everything
                if (in_array('ROLE_ADMIN', $token->getRoles())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                $user = $token->getUser();

                // consultants cannot manage anyone
                if ($user instanceof Consultant) {
                    return VoterInterface::ACCESS_DENIED;
                }

                // everybody can access a Consultant
                if ($user instanceof Consultant && $object instanceof Consultant) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                // if current user parent of tested
                if ($user->isAncestorOf($object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
