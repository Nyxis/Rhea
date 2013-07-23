<?php

namespace Extia\Bundle\TaskBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Tool form type for workflow data completion
 * @see Extia/Bundles/TaskBundle/Resources/config/services.xml
 */
class WorkflowDataType extends AbstractType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'workflow_data';
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
        $builder->add('name', 'text')
            ->add('description', 'textarea', array(
                'required' => false
            ));
    }

}
