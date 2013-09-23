<?php

namespace Extia\Bundle\DocumentBundle\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    /**
     * uploads and replace given file for this document
     * @param  UploadedFile $file
     * @return Document
     */
    public function replaceFile(UploadedFile $file)
    {
        $path   = $this->getPath();
        $newExt = $file->guessExtension();
        $oldExt = $this->getType();

        if ($newExt != $oldExt) {
            $filename = str_replace('.'.$oldExt, '.'.$newExt, $this->getName());
            $this->setName($filename);

            $this->setType($newExt);

            $path = dirname($path).'/'.$filename;
            $this->setPath($path);
        }

        $file->move(dirname($path), basename($path));

        return $this;
    }
}
