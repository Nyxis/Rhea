<?php


namespace Extia\Bundle\MissionBundle\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Created by rhea.
 * @author lesmyrmidons <lesmyrmidons@gmail.com>
 * Date: 15/08/13
 * Time: 18:38
 */
class MissionType extends AbstractType
{

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'company';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array (
            'data_class' => 'Extia\Bundle\MissionBundle\Model\Mission'
        ));
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text')
            ->add('manager', 'text', array ('required' => false))
            ->add('client', 'text', array ('required' => false))
            ->add('type', 'number', array ('required' => false))
            ->add('label', 'text', array ('required' => false));
    }
}
