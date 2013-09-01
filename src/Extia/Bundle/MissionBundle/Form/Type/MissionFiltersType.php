<?php

namespace Extia\Bundle\MissionBundle\Form\Type;

use Extia\Bundle\UserBundle\Form\Type\AdminType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Form type for mission list filters
 *
 * @see Extia/Bundles/MissionBundle/Resources/config/admin.xml
 */
class MissionFiltersType extends AdminType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'mission_filters';
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

            // display all consultants or juste mine
            $builder->add('display', 'choice', array(
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'label'    => 'mission.admin.filters.form.display',
                'choices'  => array(
                    'mine' => 'mission.admin.filters.form.mine',
                    'all'  => 'mission.admin.filters.form.all'
                )
            ));
        }

        // client name
        $builder->add('client_name', 'text', array(
            'required' => false,
            'label'    => 'mission.admin.filters.form.client_name',
        ));

        // mission label
        $builder->add('mission_label', 'text', array(
            'required' => false,
            'label'    => 'mission.admin.filters.form.mission_label',
        ));

        // manager
        $this->addInternalForm('manager', array('dir', 'ia'), $builder, array(
            'required' => false,
            'label'    => 'mission.admin.filters.form.manager',
        ));
    }

}
