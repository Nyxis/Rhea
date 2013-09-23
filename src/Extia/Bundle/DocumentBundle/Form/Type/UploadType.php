<?php

namespace Extia\Bundle\DocumentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * form type for document file upload
 * @see ExtiaDocumentBundle/Resources/config/services.xml
 */
class UploadType extends AbstractType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'document_upload';
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
        $builder->add('file', 'file', array(
            'required' => true
        ));
    }
}
