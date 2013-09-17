<?php

namespace Extia\Workflow\MissionMonitoringBundle\Form\Type;

use Extia\Bundle\TaskBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for meeting node
 * @see Extia/Workflow/MissionMonitoringBundle/Resources/workflows/meeting.xml
 */
class MeetingNodeType extends AbstractNodeType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'mission_meeting_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            $builder->create('mission_meeting_doc', 'document', array(
                    'button_label' => 'mission_monitoring.meeting.form.doc_label_button',
                    'required'     => true
                ))
                ->addModelTransformer($this->createDocumentTransformer($options))
        );

        $builder->add('report', 'mission_report_type', array(
            'required' => true,
            'label'    => false
        ));
    }
}
