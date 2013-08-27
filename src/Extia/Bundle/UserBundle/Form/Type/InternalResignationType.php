<?php

namespace Extia\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form type for internal resignation
 */
class InternalResignationType extends AdminType
{
    /**
     * @see AbstractType::getName()
     */
    public function getName()
    {
        return 'internal_resignation';
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
        $builder->add('resign_internal', 'checkbox', array(
            'required' => true,
            'label'    => 'internal.admin.form.resign_internal'
        ));

        $builder->add('leave_at', 'date', array(
            'required' => true,
            'widget'   => 'text',
            'label'    => 'internal.admin.form.resign_leave_at'
        ));

        $this->addResignationTypeForm($builder, array(
            'required' => true,
            'label'    => 'internal.admin.form.resign_type'
        ));

        $builder->add('reason', 'textarea', array(
            'required' => true,
            'label'    => 'internal.admin.form.resign_reason'
        ));

        $this->addInternalForm('assign_all_to', array('dir', 'ia', 'crh', 'pdg'), $builder, array(
            'required' => true,
            'label'    => 'internal.admin.form.resign_assign_all_to'
        ));
    }
}
