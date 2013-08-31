<?php

namespace Extia\Bundle\MissionBundle\Form\Type;

use Extia\Bundle\MissionBundle\Form\Transformer\ClientToIdsTransformer;
use Extia\Bundle\MissionBundle\Form\Transformer\ManagerToIdsTransformer;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form type for mission forms
 *
 * @see Extia/Bundles/MissionBundle/Resources/config/admin.xml
 */
class MissionType extends AbstractType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'mission_form';
    }

    /**
     * @see AbstracType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array (
            'data_class' => 'Extia\Bundle\MissionBundle\Model\Mission'
        ));
    }

    /**
     * @see AbstracType::buildForm()
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
