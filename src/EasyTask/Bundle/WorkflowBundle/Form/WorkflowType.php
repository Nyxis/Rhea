<?php

namespace EasyTask\Bundle\WorkflowBundle\Form;

use EasyTask\Bundle\WorkflowBundle\Workflow\Aggregator;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form type for workflows
 * @see EasyTask/Bundle/WorkflowBundle/Resources/config/forms.xml
 */
class WorkflowType extends AbstractType
{
    protected $aggregator;

    /**
     * construct
     * @param Aggregator $aggregator
     */
    public function __construct(Aggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    public function getName()
    {
        return 'workflow_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // options
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', 'choice', array(
            'choices'  => array_combine($this->aggregator->keys(), $this->aggregator->keys()),
            'multiple' => false,
            'expanded' => false,
            'required' => true
        ));

        $builder->add('title', 'text', array(
            'required' => true
        ));

        $builder->add('description', 'textarea', array(
            'required' => false
        ));
    }
}
