<?php

namespace Extia\Bundle\DocumentBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DocumentType extends FileType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'document';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'button_label' => 'document.form.button_label'
        ));

        return parent::setDefaultOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['button_label'] = $options['button_label'];

        return parent::buildView($view, $form, $options);
    }
}
