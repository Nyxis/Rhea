<?php


namespace Extia\Bundle\MissionBundle\Form\Type;

use Extia\Bundle\MissionBundle\Form\Transformer\ClientToIdsTransformer;
use Extia\Bundle\MissionBundle\Form\Transformer\ManagerToIdsTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * Created by rhea.
 *
 * @author lesmyrmidons <lesmyrmidons@gmail.com>
 *         Date: 15/08/13
 *         Time: 18:38
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
        return 'mission';
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
        $transformerClient = new ClientToIdsTransformer();
        $transformerManager = new ManagerToIdsTransformer();

        $builder->add('label', 'text')
        ->add($builder->create('manager', 'text', array (
                'required' => true,
            ))->addModelTransformer($transformerManager));
        $builder->add(
            $builder->create('client', 'text', array (
                'required' => true,
            ))->addModelTransformer($transformerClient));
        $builder->add('type', 'text', array ('required' => false));
    }
}
