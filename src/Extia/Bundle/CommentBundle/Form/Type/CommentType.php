<?php

namespace Extia\Bundle\CommentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type for comments
 * @see Extia/Bundles/CommentBundle/Resources/config/forms.xml
 */
class CommentType extends AbstractType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'comment_form';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array(
            'task_id' => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (empty($options['task_id'])) {
            throw new \InvalidArgumentException('You have to provide a task_id form comment.');
        }

        $builder->add('task_id', 'hidden', array(
            'data' => $options['task_id']
        ));

        $builder->add('text', 'textarea', array(
            'required' => true
        ));
    }

}
