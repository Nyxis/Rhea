<?php

namespace Extia\Workflow\LunchBundle\Form\Type;

use Extia\Bundle\TaskBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for appointement node
 * @see Extia/Workflow/LunchBundle/Resources/workflows/appointement.xml
 */
class AppointementNodeType extends AbstractNodeType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'appointement_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('meeting_place', 'text', array(
            'required'    => true,
            'label'       => 'lunch.appointement.meeting_place'
        ));

        $builder->add('meeting_date', 'datetime', array(
            'required'    => true,
            'date_widget' => 'text',
            'time_widget' => 'text',
            'input'       => 'timestamp',
            'label'       => 'lunch.appointement.meeting_date'
        ));
    }
}
