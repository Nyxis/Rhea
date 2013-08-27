<?php

namespace Extia\Bundle\CommentBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for text in textarea
 */
class TextareaDataTransformer implements DataTransformerInterface
{
    /**
     * model -> form
     * @see DataTransformerInterface::transform()
     */
    public function transform($value)
    {
        return str_replace('<br />', "\n", $value);
    }

    /**
     * form -> model
     * @see DataTransformerInterface::transform()
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return '';
        }

        $value = trim($value);
        $value = str_replace("\r", '', $value);
        $value = str_replace("\n", '<br />', $value);

        return $value;
    }
}
