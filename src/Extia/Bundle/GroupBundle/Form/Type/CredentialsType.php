<?php

namespace Extia\Bundle\GroupBundle\Form\Type;

use Extia\Bundle\GroupBundle\Model\CredentialQuery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type for credentials
 * @see ExtiaGroupBundle/Resources/config/services.xml
 */
class CredentialsType extends AbstractType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'credentials_form';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array(

        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $credentials = CredentialQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWithI18n()
            ->find();

        $typeChoices = array();
        $typeTranslation = array();
        foreach ($credentials as $credential) {
            $type = $credential->getType();
            if (empty($typeChoices[$type])) {
                $typeChoices[$type] = array();
                $typeTranslation[$type] = $credential->getTypeLabel();
            }

            $typeChoices[$type][$credential->getId()] = $credential->getLabel();
        }

        foreach ($typeChoices as $type => $choices) {
            $builder->add($type, 'choice', array(
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'label' => $typeTranslation[$type],
                'choices'  => $choices
            ));
        }
    }
}
