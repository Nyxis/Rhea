<?php

namespace Extia\Bundle\UserBundle\Form\Type;

use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\AgencyQuery;

use Extia\Bundle\MissionBundle\Model\ClientQuery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Base form type for list filters
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
abstract class AdminFilterType extends AbstractType
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
     * @return boolean [description]
     */
    public function isUserAdmin()
    {
        $user = $this->securityContext->getToken()->getUser();

        return $this->securityContext->isGranted('ROLE_ADMIN', $user);
    }

    /**
     * adds a filter on agency
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addAgencyFilter(FormBuilderInterface $builder, array $options)
    {
        $agencies = AgencyQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->select(array('Id', 'Code'))
            ->find();

        $choices = array();
        array_walk($agencies, function($row) use (&$choices) {
            $choices[$row['Id']] = 'agency.'.$row['Code'];
        });

        $builder->add('agency', 'choice', array(
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'admin.filters.form.agency',
            'choices'  => $choices
        ));
    }

    /**
     * adds a filter on an internal
     * @param string               $fieldName
     * @param array                $internalTypes
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addInternalFilter($fieldName, $internalTypes, FormBuilderInterface $builder, array $options)
    {
        $builder->add($fieldName, 'choice', array(
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'admin.filters.form.'.$fieldName,
            'choices'  => InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterByType($internalTypes)
                ->filterByActive()
                ->find()
                ->toKeyValue('Id', 'TryptedName')
        ));
    }

    /**
     * adds a filter on an client
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function addClientFilter(FormBuilderInterface $builder, array $options)
    {
        $builder->add('client', 'choice', array(
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'admin.filters.form.client',
            'choices'  => ClientQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->find()
                ->toKeyValue('Id', 'Title')
        ));
    }


}
