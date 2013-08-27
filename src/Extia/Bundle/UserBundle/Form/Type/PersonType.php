<?php
namespace Extia\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Created by rhea.
 * @author lesmyrmidons <lesmyrmidons@gmail.com>
 * Date: 30/07/13
 * Time: 12:51
 */
class PersonType extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'person';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array(
            'inherit_data' => true
        ));
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', array('label' => 'Firstname'))
            ->add('lastname', 'text', array('label' => 'Lastname'))
            ->add('email', 'email', array('label' => 'E-mail'))
            ->add('telephone', 'text', array('label' => 'Phone', 'required' => false))
            ->add('mobile', 'text', array('label' => 'Mobile Phone', 'required' => false));
    }

}
