<?php

namespace Extia\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type for list filters
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class InternalListFiltersType extends AdminType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'internal_list_filters';
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
        if ($this->isUserAdmin()) { // only admin can choice

            // display all internals or juste mine
            $builder->add('display', 'choice', array(
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'label'    => 'internal.admin.filters.form.display',
                'choices'  => array(
                    'mine' => 'internal.admin.filters.mine',
                    'all'  => 'internal.admin.filters.all'
                )
            ));
        }

        // internal name
        $builder->add('name', 'text', array(
            'required' => false,
            'label'    => 'internal.admin.filters.form.name',
        ));

        // internal type
        $this->addInternalTypeForm('internal_type', array('dir', 'ia', 'crh', 'pdg'), $builder);

        // agency
        $this->addAgencyForm($builder);

        // manager
        $this->addInternalForm('parent', array('dir', 'ia', 'crh', 'pdg'), $builder);

        // ic option
        $builder->add('with_ic', 'checkbox', array(
            'required' => false,
            'label'    => 'internal.admin.filters.form.with_ic'
        ));
    }
}
