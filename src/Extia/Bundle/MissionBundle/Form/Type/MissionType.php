<?php

namespace Extia\Bundle\MissionBundle\Form\Type;

use Extia\Bundle\MissionBundle\Form\Transformer\ClientToIdsTransformer;
use Extia\Bundle\MissionBundle\Form\Transformer\ManagerToIdsTransformer;

use Extia\Bundle\UserBundle\Form\Type\AdminType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form type for mission forms
 *
 * @see Extia/Bundles/MissionBundle/Resources/config/admin.xml
 */
class MissionType extends AdminType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'mission_form';
    }

    /**
     * @see AbstracType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array (
            'data_class' => 'Extia\Bundle\MissionBundle\Model\Mission'
        ));
    }

    /**
     * @see AbstracType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // mission label
        $builder->add('label', 'text', array(
            'required' => true,
            'label'    => 'mission.admin.form.label'
        ));

        // manager
        $this->addInternalForm('manager_id', array('dir', 'ia'), $builder, array(
            'required' => true,
            'label'    => 'mission.admin.form.manager',
        ));

        // contact name
        $builder->add('contact_name', 'text', array(
            'required' => true,
            'label'    => 'mission.admin.form.contact_name'
        ));

        // contact email
        $builder->add('contact_email', 'email', array(
            'required' => true,
            'label'    => 'mission.admin.form.contact_email'
        ));

        // contact tel
        $builder->add('contact_phone', 'text', array(
            'required' => false,
            'label'    => 'mission.admin.form.contact_phone'
        ));

        $builder->add('client', 'client_form', array(
            'required' => true,
            'label'    => false
        ));
    }
}
