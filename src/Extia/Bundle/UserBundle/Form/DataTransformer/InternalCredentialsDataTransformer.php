<?php

namespace Extia\Bundle\UserBundle\Form\DataTransformer;

use Extia\Bundle\UserBundle\Model\PersonCredential;
use Extia\Bundle\UserBundle\Model\PersonCredentialQuery;

use Extia\Bundle\GroupBundle\Model\CredentialQuery;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * data transformer used for map internal and credential type
 */
class InternalCredentialsDataTransformer implements DataTransformerInterface
{
    protected $internalId;

    /**
     * construct
     * @param int|null $groupId
     */
    public function __construct($internalId)
    {
        $this->internalId = $internalId;
    }

    /**
     * model -> form
     */
    public function transform($personCrendentials)
    {
        if (!$personCrendentials instanceof \PropelCollection) {
            throw new \InvalidArgumentException(__METHOD__.' only supports PropelCollection at param.');
        }

        if ($personCrendentials->isEmpty()) {
            return array();
        }

        $typeValues = array();
        foreach ($personCrendentials as $personCredential) {
            $type = $personCredential->getCredential()->getType();
            if (empty($typeValues[$type])) {
                $typeValues[$type] = array();
            }

            $typeValues[$type][] = $personCredential->getCredentialId();
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

        $personCredentialCollection = new \PropelCollection();
        if (empty($this->internalId)) { // creates mapping


            $selectedCredentials = CredentialQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterById($selectedCredentialsIds)
                ->find();

            foreach ($selectedCredentials as $credential) {
                $personCredential = new PersonCredential();
                $personCredential->setCredential($credential);
                $personCredentialCollection->append($personCredential);
            }
        }
        else {  // updates mapping
            foreach ($selectedCredentialsIds as $credentialId) {
                $personCrendential = PersonCredentialQuery::create()
                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                    ->filterByPersonId($this->internalId)
                    ->filterByCredentialId($credentialId)
                    ->findOneOrCreate();

                $personCredentialCollection->append($personCrendential);
            }
        }

        return $personCredentialCollection;
    }
}
