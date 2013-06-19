<?php

namespace Extia\Bundle\UserBundle\Security\Authorization\Voter;

use Extia\Bundle\UserBundle\Model\User\Internal;
use Extia\Bundle\UserBundle\Model\User\Consultant;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Voter which grant access of a person data
 * @see Extia/Bundle/UserBundle/Resources/config/services.xml
 */
class InternalVoter implements VoterInterface
{
    public function supportsAttribute($attribute)
    {
        return 'USER' === $attribute;
    }

    public function supportsClass($class)
    {
        return $class instanceof Internal;
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute) && $this->supportsClass($object)) {

                // everybody can access a Consultant
                if ($object instanceof Consultant) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                $user = $token->getUser();

                // i can access on me
                if ($user->equals($object)) {
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
