<?php

namespace Extia\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Form type for consultants mission switching
 *
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class ChangeMissionType extends AdminType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'change_mission_form';
    }

    /**
     * @see AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(

        ));
    }

    /**
     * @see AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // end date
        $builder->add('end_date', 'date', array(
            'required' => true,
            'widget'   => 'text',
            'format'   => 'dd/MM/yyyy',
            'label'    => 'consultant.change_mission.form.end_date'
        ));

        // intercontract ?
        $builder->add('next_intercontract', 'checkbox', array(
            'required' => false,
            'label'    => 'consultant.change_mission.form.next_intercontract'
        ));

        // next mission
        $this->addMissionForm('next_mission_id', $builder, array(
            'required' => false,
            'label'    => 'consultant.change_mission.form.next_mission_id'
        ));

        // begin date
        $builder->add('next_begin_date', 'date', array(
            'required' => false,
            'widget'   => 'text',
            'format'   => 'dd/MM/yyyy',
            'label'    => 'consultant.change_mission.form.next_begin_date'
        ));



    }
}
