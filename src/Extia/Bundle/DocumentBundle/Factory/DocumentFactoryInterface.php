<?php

namespace Extia\Bundle\DocumentBundle\Factory;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * interface for document factory class
 */
interface DocumentFactoryInterface
{
    /**
     * returns an instanced DocumentDataTransformer
     * @param  string                  $documentName    name of output doc
     * @param  string                  $uploadDirectory optionnal upload directory
     * @return DocumentDataTransformer
     * @see Extia\Bundle\DocumentBundle\Form\DataTransformer\DocumentDataTransformer
     */
    public function createDataTransformer($documentName, $uploadDirectory = null);

    /**
     * returns document directory absolute path
     * @return string
     */
    public function getDirectory();

    /**
     * tests if given uploaded file is supported or not
     * @param  UploadedFile $file
     * @return boolean
     */
    public function supports(UploadedFile $file);

    /**
     * create document name from uploadedfile
     * @param  UploadedFile $file
     * @param  string       $documentName document base name
     * @return string
     */
    public function makeName(UploadedFile $file, $documentName);
}
