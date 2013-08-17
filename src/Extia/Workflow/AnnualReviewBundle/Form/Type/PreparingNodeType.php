<?php

namespace Extia\Workflow\AnnualReviewBundle\Form\Type;

use Extia\Bundle\TaskBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for initiation node
 * @see Extia/Workflow/AnnualReviewBundle/Resources/workflows/initiation.xml
 */
class PreparingNodeType extends AbstractNodeType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'annual_review_preparing_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create('annual_review_doc', 'document', array(
                    'label'        => 'annual_review_preparing.form.document',
                    'button_label' => 'annual_review_preparing.form.doc_label_button',
                    'required'     => true
                ))
                ->addModelTransformer($this->createDocumentTransformer($options))
        );

        $builder->add('manager_id', 'choice', array(
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'choices'  => $this->getManagersChoices(),
            'label'    => 'annual_review_preparing.form.manager_id'
        ));

        $builder->add('meeting_date', 'date', array(
            'required' => true,
            'widget'   => 'text',
            'input'    => 'timestamp',
            'format'   => 'dd/MM/yyyy',
            'label'    => 'annual_review_preparing.form.meeting_date'
        ));
    }
}
