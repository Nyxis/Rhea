<?php
namespace Extia\Bundle\UserBundle\Form\Type;

use Extia\Bundle\UserBundle\Model\ConsultantQuery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type for consultant model
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class ConsultantType extends AdminType
{
    /**
     * @see AbstracType::getName()
     */
    public function getName()
    {
        return 'consultant_form';
    }

    /**
     * @see AbstractType::setDefaultOptions()
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array(
            'consultant_id' => null
        ));
    }

    /**
     * @see AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('lastname', 'text', array(
            'required' => true,
            'label'    => 'consultant.admin.form.lastname'
        ));

        $builder->add('firstname', 'text', array(
            'required' => true,
            'label'    => 'consultant.admin.form.firstname'
        ));

        $builder->add('birthdate', 'birthday', array(
            'required' => true,
            'widget'   => 'text',
            'format'   => 'dd/MM/yyyy',
            'label'    => 'consultant.admin.form.birthdate'
        ));

        $builder->add('image', 'file', array(
            'required' => false,
            'label'    => 'consultant.admin.form.image',
            'mapped'   => false
        ));

        $builder->add('email', 'email', array(
            'required' => true,
            'label'    => 'consultant.admin.form.email'
        ));

        $builder->add('telephone', 'text', array(
            'required' => false,
            'label'    => 'consultant.admin.form.telephone'
        ));

        $builder->add('mobile', 'text', array(
            'required' => false,
            'label'    => 'consultant.admin.form.mobile'
        ));

        $builder->add('contractBeginDate', 'date', array(
            'required' => true,
            'widget'   => 'text',
            'format'   => 'dd/MM/yyyy',
            'label'    => 'consultant.admin.form.contract_begin'
        ));

        $builder->add('job', 'text', array(
            'required' => true,
            'label'    => 'consultant.admin.form.job'
        ));

        $this->addAgencyForm($builder, array(
            'required' => true,
            'label'    => 'consultant.admin.form.agency'
        ));

        $this->addInternalForm('crh_id', array('crh'), $builder, array(
            'required' => true,
            'label'    => 'consultant.admin.form.crh'
        ));

        $builder->add('coopted_by_id', 'choice', array(
            'required' => false,
            'expanded' => false,
            'multiple' => false,
            'label'    => 'consultant.admin.form.coopted_by',
            'choices'  => ConsultantQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->find()
                ->toKeyValue('Id', 'LongName')
        ));

        // mission
        if (empty($options['consultant_id'])) {
            $builder->add('begin_at', 'date', array(
                'required' => true,
                'widget'   => 'text',
                'format'   => 'dd/MM/yyyy',
                'label'    => 'consultant.admin.form.begin_at',
                'mapped'   => false
            ));

            $builder->add('on_profile', 'checkbox', array(
                'required' => false,
                'label'    => 'consultant.admin.form.on_profile',
                'mapped'   => false
            ));

            $this->addInternalForm('manager_id', array('dir', 'ia'), $builder, array(
                'required' => false,
                'label'    => 'consultant.admin.form.manager',
                'mapped'   => false
            ));

            $this->addMissionForm('mission', $builder, array(
                'required' => false,
                'label'    => 'consultant.admin.form.mission',
                'mapped'   => false
            ));
        }

        // credentials
        $this->addGroupForm($builder, array(
            'required' => true,
            'label'    => 'consultant.admin.form.group'
        ));

        $this->addPersonCredentialForm($builder, array(
            'required'    => false,
            'label'       => 'consultant.admin.form.credentials',
            'internal_id' => $options['consultant_id']
        ));

        // tasks
        if (empty($options['consultant_id'])) {
            $builder->add('create_crh_monitoring', 'checkbox', array(
                'required' => false,
                'label'    => 'consultant.admin.form.create_crh_monitoring',
                'mapped'   => false
            ));

            $builder->add('create_annual_review', 'checkbox', array(
                'required' => false,
                'label'    => 'consultant.admin.form.create_annual_review',
                'mapped'   => false
            ));
        }

        // resignation
        if (!empty($options['consultant_id'])) {
            $builder->add('resignation', 'consultant_resignation', array(
                'required' => false,
                'mapped'   => false,
                'label'    => false
            ));
        }
    }
}
