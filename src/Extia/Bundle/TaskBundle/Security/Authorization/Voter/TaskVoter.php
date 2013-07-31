<?php

namespace Extia\Bundle\TaskBundle\Security\Authorization\Voter;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\UserBundle\Model\Internal;

use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Voter use to tests if current user can access a task
 * @see Extia/Bundle/TaskBundle/Resources/config/services.xml
 */
class TaskVoter implements VoterInterface
{
    public static $taskAccess = array(
        'TASK_READ'  => 'read',
        'TASK_WRITE' => 'write',
        'TASK_EXEC'  => 'exec'
    );

    /**
     * @see VoterInterface::supportsAttribute()
     */
    public function supportsAttribute($attribute)
    {
        return in_array($attribute, array_keys(self::$taskAccess))
            || $attribute == 'TASK_CREATION';
    }

    /**
     * @see VoterInterface::supportsClass()
     */
    public function supportsClass($class)
    {
        return $class instanceof Task
            || $class instanceof Internal;
    }

    /**
     * @see VoterInterface::vote()
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (!$this->supportsClass($object)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if ($this->supportsAttribute($attribute)) {

                // admin can access everything
                if (in_array('ROLE_ADMIN', $token->getRoles())) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                // only assigned can exec a task
                $user = $token->getUser();
                if ($attribute == 'TASK_EXEC' && $user->getId() === $object->getAssignedTo()) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                $tasksCredentials = $this->getTaskCredentials($token);

                // tests if user can create a task
                if ($attribute == 'TASK_CREATION' && !empty($tasksCredentials)) {
                    return VoterInterface::ACCESS_GRANTED;
                }

                $workflowType = $object->getNode()->getWorkflow()->getType();
                if (in_array($workflowType, $tasksCredentials[self::$taskAccess[$attribute]])) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }

    /**
     * returns list of task access credentials as array
     * @param  TokenInterface $token
     * @return array(         'access1' => array(workflow_type1, ....), 'access2' => ..... )
     */
    public function getTaskCredentials(TokenInterface $token)
    {
        $taskRoles = array();
        foreach (self::$taskAccess as $access) {
            $taskRoles[$access] = array();
        }

        $roleCollection = $token->getRoles();
        foreach ($roleCollection as $role) {
            foreach (self::$taskAccess as $access) {
                $regex = sprintf('/^role_workflow_(\w+)_%s$/', $access);
                if (preg_match($regex, strtolower($role->getRole()), $matches)) {
                    $taskRoles[$access][] = $matches[1];
                }
            }
        }

        return $taskRoles;
    }
}
