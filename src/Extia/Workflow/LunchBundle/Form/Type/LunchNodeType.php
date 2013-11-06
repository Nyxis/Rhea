<?php

namespace Extia\Workflow\LunchBundle\Form\Type;

use Extia\Bundle\TaskBundle\Form\Type\AbstractNodeType;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * form type for lunch node
 * @see Extia/Workflow/LunchBundle/Resources/workflows/lunch.xml
 */
class LunchNodeType extends AbstractNodeType
{
    /**
     * {@inherit_doc}
     */
    public function getName()
    {
        return 'lunch_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('confirmation', 'hidden', array(
            'required' => true
        ));
    }
}
