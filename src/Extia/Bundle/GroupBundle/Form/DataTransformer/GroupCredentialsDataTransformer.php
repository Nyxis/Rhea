<?php

namespace Extia\Bundle\GroupBundle\Form\DataTransformer;

use Extia\Bundle\GroupBundle\Model\CredentialQuery;
use Extia\Bundle\GroupBundle\Model\GroupCredential;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * data transformer used for map group and credential type
 */
class GroupCredentialsDataTransformer implements DataTransformerInterface
{
    protected $groupId;

    /**
     * construct
     * @param int|null $groupId
     */
    public function __construct($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * model -> form
     */
    public function transform($groupCrendentials)
    {
        if (!$groupCrendentials instanceof \PropelCollection) {
            throw new \InvalidArgumentException(__METHOD__.' only supports PropelCollection at param.');
        }

        if ($groupCrendentials->isEmpty()) {
            return array();
        }

        $typeValues = array();
        foreach ($groupCrendentials as $groupCredential) {
            $type = $groupCredential->getCredential()->getType();
            if (empty($typeValues[$type])) {
                $typeValues[$type] = array();
            }

            $typeValues[$type][] = $groupCredential->getCredentialId();
        }

        return $typeValues;
    }

    /**
     * form -> model
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($formData)
    {
        $selectedCredentialsIds = array();

        foreach ($formData as $field => $credentialIds) {
            $selectedCredentialsIds = array_merge($selectedCredentialsIds, $credentialIds);
        }

        $groupCredentialCollection = new \PropelCollection();
        if (empty($this->groupId)) { // creates mapping
            $selectedCredentials = CredentialQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterById($selectedCredentialsIds)
                ->find();

            foreach ($selectedCredentials as $credential) {
                $groupCrendential = new GroupCredential();
                $groupCrendential->setCredential($credential);
                $groupCredentialCollection->append($groupCrendential);
            }
        } else {  // updates mapping
            foreach ($selectedCredentialsIds as $credentialId) {
                $groupCrendential = GroupCredential::create()
                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                    ->filterByGroupId($this->groupId)
                    ->filterByCredentialId($credentialId)
                    ->findOneOrCreate();

                $groupCredentialCollection->append($groupCrendential);
            }
        }

        return $groupCredentialCollection;
    }
}
