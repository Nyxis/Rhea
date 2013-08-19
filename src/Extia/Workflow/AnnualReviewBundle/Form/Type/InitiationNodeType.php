<?php

namespace Extia\Workflow\AnnualReviewBundle\Form\Type;

use Extia\Bundle\TaskBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for initiation node
 * @see Extia/Workflow/AnnualReviewBundle/Resources/workflows/initiation.xml
 */
class InitiationNodeType extends AbstractNodeType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'annual_review_initiation_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('workflow', 'workflow_data', array(
            'required' => true
        ));

        $builder->add('user_target_id', 'choice', array(
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'choices'  => $this->getConsultantsChoices(),
            'label'    => 'annual_review_initiation.form.user_target_id'
        ));

        $builder->add('next_date', 'date', array(
            'required' => true,
            'widget'   => 'text',
            'input'    => 'timestamp',
            'format'   => 'dd/MM/yyyy',
            'label'    => 'annual_review_initiation.form.next_date'
        ));
    }
}
