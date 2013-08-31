<?php

namespace Extia\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form type for consultant resignation
 */
class ConsultantResignationType extends AdminType
{
    /**
     * @see AbstractType::getName()
     */
    public function getName()
    {
        return 'consultant_resignation';
    }

    /**
     * @see AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array(

        ));
    }

    /**
     * @see AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('resign_consultant', 'checkbox', array(
            'required' => true,
            'label'    => 'consultant.admin.form.resign_consultant'
        ));

        $builder->add('leave_at', 'date', array(
            'required' => true,
            'widget'   => 'text',
            'label'    => 'consultant.admin.form.resign_leave_at'
        ));

        $this->addResignationTypeForm($builder, array(
            'required' => true,
            'label'    => 'consultant.admin.form.resign_type'
        ));

        $builder->add('reason', 'textarea', array(
            'required' => true,
            'label'    => 'consultant.admin.form.resign_reason'
        ));

        $builder->add('options', 'choice', array(
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'label'    => 'consultant.admin.form.resign_options',
            'choices'  => array(
                'close_tasks' => 'consultant.admin.form.resign_options_choices.close_tasks',
                'end_mission' => 'consultant.admin.form.resign_options_choices.end_mission'
            )
        ));
    }
}
