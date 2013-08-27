<?php

namespace Extia\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Form type for list filters
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class ConsultantListFiltersType extends AdminType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'consultant_list_filters';
    }

    /**
     * @see AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array());
    }

    /**
     * @see AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // display all consultants or juste mine
        $builder->add('display', 'choice', array(
            'required' => true,
            'expanded' => true,
            'multiple' => false,
            'label'    => 'consultant.admin.filters.form.display',
            'choices'  => array(
                'mine' => 'consultant.admin.filters.mine',
                'all'  => 'consultant.admin.filters.all'
            )
        ));

        // consultant name
        $builder->add('name', 'text', array(
            'required' => false,
            'label'    => 'consultant.admin.filters.form.name',
        ));

        // consultant status
        $builder->add('status', 'choice', array(
            'required' => true,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'consultant.admin.filters.form.status',
            'choices'  => array(
                'active'    => 'user_status.active',
                'ic'        => 'user_status.ic',
                'placed'    => 'user_status.placed',
                'recruited' => 'user_status.recruited',
                'resigned'  => 'user_status.resigned',
            )
        ));

        // agency
        $this->addAgencyForm($builder);

        // manager
        $this->addInternalForm('manager', array('dir', 'ia'), $builder);

        // crh
        $this->addInternalForm('crh', array('crh'), $builder);

        // client
        $this->addClientForm($builder);
    }

}
