<?php

namespace Extia\Bundle\TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Tool form type for task differing
 * @see Extia/Bundles/TaskBundle/Resources/config/services.xml
 */
class DifferTaskType extends AbstractType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'differ_task';
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
        $builder->add('task_id', 'text', array());

        $builder->add('differ_date', 'date', array(
            'required' => true,
            'widget'   => 'text',
            'input'    => 'timestamp',
            'format'   => 'dd/MM/yyyy',
            'label'    => 'task.differ.form.differ_date',
        ));

        $builder->add('comment', 'textarea', array(
            'label'    => 'task.differ.form.comment',
            'required' => true
        ));
    }

}
