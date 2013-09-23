<?php

namespace Extia\Bundle\DocumentBundle\Factory;

use Extia\Bundle\DocumentBundle\Form\DataTransformer\DocumentDataTransformer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * interface for document factory class
 */
class DocumentFactory implements DocumentFactoryInterface
{
    protected $uploadDirectory;
    protected $supportedExtensions;

    /**
     * construct
     * @param string $uploadDirectory
     * @param array  $supportedExtensions
     */
    public function __construct($uploadDirectory, array $supportedExtensions)
    {
        $this->uploadDirectory     = $uploadDirectory;
        $this->supportedExtensions = $supportedExtensions;
    }

    /**
     * @see DocumentFactoryInterface::createDataTransformer()
     */
    public function createDataTransformer($documentName, $uploadDirectory = null)
    {
        return new DocumentDataTransformer($this, $documentName,
            empty($uploadDirectory) ? $this->uploadDirectory : $uploadDirectory
        );
    }

    /**
     * @see DocumentFactoryInterface::getDirectory()
     */
    public function getDirectory()
    {
        return $this->uploadDirectory;
    }

    /**
     * @see DocumentFactoryInterface::supports()
     */
    public function supports(UploadedFile $file)
    {
        return in_array($file->guessExtension(), $this->supportedExtensions);
    }

    /**
     * @see DocumentFactoryInterface::makeName()
     */
    public function makeName(UploadedFile $file, $documentName)
    {
        if (!$this->supports($file)) {
            throw new \InvalidArgumentException(sprintf('Given file is invalid, "%s" given', $file->getClientOriginalName()));
        }

        return sprintf('%s_%s.%s',
            date('Y_m_d'), $documentName, $file->guessExtension()
        );
    }
}
