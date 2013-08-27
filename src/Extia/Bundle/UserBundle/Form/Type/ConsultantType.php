<?php
namespace Extia\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 */
class ConsultantType extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'consultant';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array (
                                          'data_class' => 'Extia\Bundle\UserBundle\Model\Consultant'
                                      ));
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('person', 'person', array ('label' => false));
//                ->add('manager', 'text', array ('label' => 'Manager'))
//                ->add('rh', 'text', array ('label' => 'R.H.'));

    }


}
