<?php

namespace Extia\Bundle\MissionBundle\Form\Type;

use Extia\Bundle\UserBundle\Form\Type\AdminType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * base form type for mission forms
 */
abstract class MissionType extends AdminType
{
    /**
     * @see AbstracType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // mission label
        $builder->add('label', 'text', array(
            'required' => true,
            'label'    => 'mission.admin.form.label'
        ));

        // manager
        $this->addInternalForm('manager_id', array('dir', 'ia'), $builder, array(
            'required' => true,
            'label'    => 'mission.admin.form.manager',
        ));

        // contact name
        $builder->add('contact_name', 'text', array(
            'required' => true,
            'label'    => 'mission.admin.form.contact_name'
        ));

        // contact email
        $builder->add('contact_email', 'email', array(
            'required' => true,
            'label'    => 'mission.admin.form.contact_email'
        ));

        // contact tel
        $builder->add('contact_phone', 'text', array(
            'required' => false,
            'label'    => 'mission.admin.form.contact_phone'
        ));
    }
}
