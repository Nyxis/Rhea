<?php

namespace Extia\Workflow\CrhMonitoringBundle\Form\Type;

use Extia\Bundle\ExtraWorkflowBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for meeting node
 * @see Extia/Workflow/CrhMonitoringBundle/Resources/workflows/meeting.xml
 */
class MeetingNodeType extends AbstractNodeType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'meeting_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('next_meeting', 'choice', array(
            'required' => true,
            'multiple' => false,
            'expanded' => false,
            'choices'  => array(
                '3'  => '3 '.$this->translator->trans('month'),
                '6'  => '6 '.$this->translator->trans('month'),
                '1'  => '1 '.$this->translator->trans('month')
            )
        ));
    }
}
