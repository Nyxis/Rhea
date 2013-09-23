<?php

namespace Extia\Workflow\MissionMonitoringBundle\Form\Type;

use Extia\Bundle\TaskBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for appointement node
 * @see Extia/Workflow/MissionMonitoringBundle/Resources/workflows/appointement.xml
 */
class AppointementNodeType extends AbstractNodeType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'mission_appointement_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('contact_name', 'text', array(
            'required' => true,
            'label'    => 'mission_monitoring.appointement.contact_name'
        ));

        $builder->add('contact_email', 'email', array(
            'required' => false,
            'label'    => 'mission_monitoring.appointement.contact_email'
        ));

        $builder->add('contact_tel', 'text', array(
            'required' => false,
            'label'    => 'mission_monitoring.appointement.contact_tel'
        ));

        $builder->add('meeting_date', 'datetime', array(
            'required'    => true,
            'date_widget' => 'text',
            'time_widget' => 'text',
            'input'       => 'timestamp',
            'label'       => 'mission_monitoring.appointement.meeting_date'
        ));
    }
}
