<?php

namespace Extia\Workflow\AnnualReviewBundle\Form\Type;

use Extia\Bundle\TaskBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for preparing node
 * @see Extia/Workflow/AnnualReviewBundle/Resources/workflows/preparing.xml
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
                    'label'        => 'annual_review.preparing.form.document',
                    'button_label' => 'annual_review.preparing.form.doc_label_button',
                    'required'     => true
                ))
                ->addModelTransformer($this->createDocumentTransformer($options))
        );

        $builder->add('manager_id', 'choice', array(
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'choices'  => $this->getManagersChoices(),
            'label'    => 'annual_review.preparing.form.manager_id'
        ));

        $builder->add('meeting_date', 'datetime', array(
            'required'    => true,
            'date_widget' => 'text',
            'time_widget' => 'text',
            'input'       => 'timestamp',
            'label'       => 'annual_review.preparing.form.meeting_date'
        ));
    }
}
