<?php

namespace Extia\Bundle\GroupBundle\Form\Type;

use Extia\Bundle\GroupBundle\Form\DataTransformer\GroupCredentialsDataTransformer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type for groups
 * @see ExtiaGroupBundle/Resources/config/services.xml
 */
class GroupType extends AbstractType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'group_form';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array(
            'data_class' => 'Extia\Bundle\GroupBundle\Model\Group',
            'group_id'   => null
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // group label
        $builder->add('label', 'text', array(
            'required' => true
        ));

        // credentials throught transformer
        $builder->add(
            $builder->create('GroupCredentials', 'credentials_form')
                ->addModelTransformer(new GroupCredentialsDataTransformer($options['group_id']))
        );
    }
}
