<?php

namespace Extia\Bundle\MissionBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form type for client forms
 *
 * @see Extia/Bundles/MissionBundle/Resources/config/admin.xml
 */
class ClientType extends AbstractType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'client_form';
    }

    /**
     * @see AbstracType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array (
            'data_class' => 'Extia\Bundle\MissionBundle\Model\Client'
        ));
    }

    /**
     * @see AbstracType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text')
            ->add('zipCode', 'number', array ('required' => false))
            ->add('city', 'text', array ('required' => false))
            ->add('image', 'file', array ('required' => false))
        ;
    }
}
