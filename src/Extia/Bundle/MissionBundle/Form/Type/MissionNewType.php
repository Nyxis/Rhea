<?php

namespace Extia\Bundle\MissionBundle\Form\Type;

use Extia\Bundle\MissionBundle\Form\Transformer\ClientToIdsTransformer;
use Extia\Bundle\MissionBundle\Form\Transformer\ManagerToIdsTransformer;

use Extia\Bundle\UserBundle\Form\Type\AdminType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form type for mission creation forms
 *
 * @see Extia/Bundles/MissionBundle/Resources/config/admin.xml
 */
class MissionNewType extends MissionType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'mission_new_form';
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
        parent::buildForm($builder, $options);

        $this->addClientForm('client_id', $builder, array(
            'required' => false,
            'mapped'   => false,
            'label'    => 'mission.admin.form.client_id'
        ));

        $builder->add('client', 'client_form', array(
            'required' => false,
            'mapped'   => false,
            'label'    => false
        ));
    }
}
