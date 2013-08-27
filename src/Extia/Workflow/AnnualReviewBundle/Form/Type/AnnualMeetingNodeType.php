<?php

namespace Extia\Workflow\AnnualReviewBundle\Form\Type;

use Extia\Bundle\TaskBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for annual meeting node
 * @see Extia/Workflow/AnnualReviewBundle/Resources/workflows/annual_meeting.xml
 */
class AnnualMeetingNodeType extends AbstractNodeType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'annual_review_annual_meeting_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create('annual_review_doc', 'document', array(
                    'label'        => 'annual_review_annual_meeting.form.document',
                    'button_label' => 'annual_review_annual_meeting.form.doc_label_button',
                    'required'     => true
                ))
                ->addModelTransformer($this->createDocumentTransformer($options))
        );
    }
}
