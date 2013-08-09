<?php

namespace Extia\Bundle\DocumentBundle\Model;

use Extia\Bundle\DocumentBundle\Model\om\BaseDocument;

class Document extends BaseDocument
{
    /**
     * return document name without date prefix
     * @return string
     */
    public function getSimpleName()
    {
        return preg_replace('/^([0-9]{4}_[0-9]{2}_[0-9]{2}_)/', '', $this->getName());
    }
}
