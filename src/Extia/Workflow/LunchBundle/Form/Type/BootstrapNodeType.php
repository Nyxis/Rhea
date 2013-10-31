<?php

namespace Extia\Workflow\LunchBundle\Form\Type;

use Extia\Bundle\TaskBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for bootstrap node
 * @see Extia/Workflow/LunchBundle/Resources/workflows/bootstrap.xml
 */
class BootstrapNodeType extends AbstractNodeType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'bootstrap_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('workflow', 'workflow_data', array(
            'required'     => true
        ));

        $builder->add('mission_target_id', 'choice', array(
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'choices'  => $this->getMissionsChoices(),
            'label'    => 'lunch.bootstrap.user_target_id'
        ));

        $builder->add('next_date', 'date', array(
            'required' => true,
            'widget'   => 'text',
            'input'    => 'timestamp',
            'format'   => 'dd/MM/yyyy',
            'label'    => 'crh_monitoring.bootstrap.next_date'
        ));
    }
}
