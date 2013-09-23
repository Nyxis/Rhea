<?php

namespace Extia\Workflow\MissionMonitoringBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form type for mission order report
 * @see ExtiaWorkflowMissionMonitoringBundle/Resources/workflows/meeting.xml
 */
class ReportType extends AbstractType
{
    /**
     * @see AbstractType::getName()
     */
    public function getName()
    {
        return 'mission_report_type';
    }

    /**
     * @see AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array(
            'data_class' => 'Extia\Workflow\MissionMonitoringBundle\Model\MissionOrderReport'
        ));
    }

    /**
     * @see AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            '4' => 'mission_monitoring.meeting.report.choices.sup',
            '3' => 'mission_monitoring.meeting.report.choices.complient',
            '2' => 'mission_monitoring.meeting.report.choices.part_complient',
            '1' => 'mission_monitoring.meeting.report.choices.uncomplient',
            '0' => 'mission_monitoring.meeting.report.choices.unapplicable',
        );

        $builder->add('admin_rating', 'choice', array(
            'required' => true,
            'expanded' => true,
            'multiple' => false,
            'choices'  => $choices,
            'label'    => 'mission_monitoring.meeting.report.admin_rating'
        ));

        $builder->add('global_rating', 'choice', array(
            'required' => true,
            'expanded' => true,
            'multiple' => false,
            'choices'  => $choices,
            'label'    => 'mission_monitoring.meeting.report.global_rating'
        ));

        $builder->add('reactivity_rating', 'choice', array(
            'required' => true,
            'expanded' => true,
            'multiple' => false,
            'choices'  => $choices,
            'label'    => 'mission_monitoring.meeting.report.reactivity_rating'
        ));

        $builder->add('expertise_rating', 'choice', array(
            'required' => true,
            'expanded' => true,
            'multiple' => false,
            'choices'  => $choices,
            'label'    => 'mission_monitoring.meeting.report.expertise_rating'
        ));
    }
}
