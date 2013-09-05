<?php

namespace Extia\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for internal editing
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class InternalType extends AdminType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'internal_form';
    }

    /**
     * @see AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'internal_id'      => null,
            'with_resign'      => false,
            'data_class'       => 'Extia\Bundle\UserBundle\Model\Internal',
            'validation_group' => 'Default'
        ));
    }

    /**
     * @see AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('lastname', 'text', array(
            'required' => true,
            'label'    => 'internal.admin.form.lastname'
        ));

        $builder->add('firstname', 'text', array(
            'required' => true,
            'label'    => 'internal.admin.form.firstname'
        ));

        $builder->add('birthdate', 'date', array(
            'required'    => true,
            'widget'      => 'text',
            'label'       => 'internal.admin.form.birthdate',
            'constraints' => array(
                new Assert\Date(), new Assert\NotBlank()
            )
        ));

        $builder->add('image', 'file', array(
            'required' => false,
            'label'    => 'internal.admin.form.image',
            'mapped'   => false
        ));

        $builder->add('trigram', 'text', array(
            'required' => true,
            'label'    => 'internal.admin.form.trigram'
        ));

        $builder->add('contractBeginDate', 'date', array(
            'required'    => true,
            'widget'      => 'text',
            'format'      => 'dd/MM/yyyy',
            'label'       => 'consultant.admin.form.contract_begin',
            'constraints' => array(
                new Assert\Date(), new Assert\NotBlank()
            )
        ));

        $this->addInternalTypeForm('person_type_id', array('dir', 'ia', 'crh', 'pdg'), $builder, array(
            'required' => true
        ));

        $builder->add('job', 'text', array(
            'required' => true,
            'label'    => 'internal.admin.form.job'
        ));

        $this->addAgencyForm($builder, array(
            'required' => true
        ));

        $this->addInternalForm('parent', array('dir', 'ia', 'crh', 'pdg'), $builder, array(
            'required' => true,
            'mapped'   => false
        ));

        $builder->add('email', 'email', array(
            'required' => true,
            'label'    => 'internal.admin.form.email'
        ));

        $builder->add('telephone', 'text', array(
            'required' => false,
            'label'    => 'internal.admin.form.telephone'
        ));

        $builder->add('mobile', 'text', array(
            'required' => false,
            'label'    => 'internal.admin.form.mobile'
        ));

        if ($this->isUserAdmin()) {
            $builder->add('update_password', 'checkbox', array(
                'mapped'   => false,
                'required' => false,
                'label'    => 'internal.admin.form.update_password'
            ));

            $builder->add('password', 'repeated', array(
                'type'           => 'password',
                'mapped'         => false,
                'options'        => array(
                    'required' => false,
                    'label'    => false
                ),
                'first_options'  => array(
                    'label' => 'internal.admin.form.password'
                ),
                'second_options' => array(
                    'label' => 'internal.admin.form.confirm_password'
                )
            ));
        }

        $this->addGroupForm($builder, array(
            'required' => true,
            'label'    => 'internal.admin.form.group'
        ));

        $this->addPersonCredentialForm($builder, array(
            'required'    => false,
            'label'       => 'internal.admin.form.credentials',
            'internal_id' => $options['internal_id']
        ));

        if (!empty($options['with_resign'])) {
            $builder->add('resignation', 'internal_resignation', array(
                'required' => false,
                'mapped'   => false,
                'label'    => false
            ));
        }
    }
}
