<?php

namespace Extia\Bundle\UserBundle\Form\Type;

use Extia\Bundle\UserBundle\Form\DataTransformer\InternalCredentialsDataTransformer;
use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\AgencyQuery;
use Extia\Bundle\UserBundle\Model\PersonTypeQuery;

use Extia\Bundle\GroupBundle\Model\GroupQuery;
use Extia\Bundle\MissionBundle\Model\ClientQuery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Base form type for all admin form
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
abstract class AdminType extends AbstractType
{
    protected $securityContext;

    /**
     * construct
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * tests id current user is admin
     * @return boolean
     */
    public function isUserAdmin()
    {
        $user = $this->securityContext->getToken()->getUser();

        return $this->securityContext->isGranted('ROLE_ADMIN', $user);
    }

    /**
     * adds a form for internal type
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addInternalTypeForm($fieldName, $codes, $builder, $options = array())
    {
        $internalTypes = PersonTypeQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->select(array('Id', 'Code'))
            ->filterByCode($codes)
            ->find();

        $choices = array();
        array_walk($internalTypes, function($row) use (&$choices) {
            $choices[$row['Id']] = 'person_type.'.$row['Code'];
        });

        $builder->add($fieldName, 'choice', array_replace_recursive(array(
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'admin.form.internal_type',
            'choices'  => $choices
        ), $options));
    }

    /**
     * adds a form for agency
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addAgencyForm(FormBuilderInterface $builder, array $options = array())
    {
        $agencies = AgencyQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->select(array('Id', 'Code'))
            ->find();

        $choices = array();
        array_walk($agencies, function($row) use (&$choices) {
            $choices[$row['Id']] = 'admin.form.agency_choices.'.$row['Code'];
        });

        $builder->add('agency', 'choice', array_replace_recursive(array(
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'admin.form.agency',
            'choices'  => $choices
        ), $options));
    }

    /**
     * adds a form for an internal
     * @param string               $fieldName
     * @param array                $internalTypes
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addInternalForm($fieldName, $internalTypes, FormBuilderInterface $builder, array $options = array())
    {
        $builder->add($fieldName, 'choice', array_replace_recursive(array(
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'admin.form.'.$fieldName,
            'choices'  => InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterByType($internalTypes)
                ->filterByActive()
                ->orderByTreeLeft()
                ->find()
                ->toKeyValue('Id', 'TryptedName')
        ), $options));
    }

    /**
     * adds a form for an client
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addClientForm(FormBuilderInterface $builder, array $options = array())
    {
        $builder->add('client', 'choice', array_replace_recursive(array(
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'admin.form.client',
            'choices'  => ClientQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->find()
                ->toKeyValue('Id', 'Title')
        ), $options));
    }

    /**
     * adds a form for a group
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addGroupForm(FormBuilderInterface $builder, array $options = array())
    {
        $builder->add('group_id', 'choice', array_replace_recursive(array(
            'required' => true,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'admin.form.client',
            'choices'  => GroupQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->find()
                ->toKeyValue('Id', 'Label')
        ), $options));
    }

    /**
     * adds a form for a person credential list
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addPersonCredentialForm(FormBuilderInterface $builder, array $options = array())
    {
        $internalId = null;
        if (!empty($options['internal_id'])) {
            $internalId = $options['internal_id'];
        }
        unset($options['internal_id']);

        $builder->add($builder
            ->create('PersonCredentials', 'credentials_form', $options)
            ->addModelTransformer(new InternalCredentialsDataTransformer($internalId)
        ));
    }

    /**
     * adds a form for resignation type
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addResignationTypeForm(FormBuilderInterface $builder, array $options = array())
    {
        $builder->add('resignation_code', 'choice', array_replace_recursive(array(
            'required' => true,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'admin.form.resignation_code',
            'choices'  => array(
                'resignation'  => 'admin.form.resignation_code_choice.resignation',
                'end_test'     => 'admin.form.resignation_code_choice.end_test',
                'end_contract' => 'admin.form.resignation_code_choice.end_contract',
                'end_training' => 'admin.form.resignation_code_choice.end_training',
                'leaving'      => 'admin.form.resignation_code_choice.leaving'
            )
        ), $options));
    }
}
