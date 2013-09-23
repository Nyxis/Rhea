<?php

namespace Extia\Bundle\DocumentBundle\Form\DataTransformer;

use Extia\Bundle\DocumentBundle\Factory\DocumentFactoryInterface;
use Extia\Bundle\DocumentBundle\Model\DocumentQuery;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for files into documents
 */
class DocumentDataTransformer implements DataTransformerInterface
{
    protected $documentFactory;
    protected $documentDir;
    protected $documentName;

    /**
     * construct with base document path in filesystem
     * @param string $documentName
     * @param string $documentDir
     */
    public function __construct(DocumentFactoryInterface $documentFactory, $documentName, $documentDir)
    {
        $this->documentFactory = $documentFactory;
        $this->documentName    = $documentName;
        $this->documentDir     = $documentDir;
    }

    /**
     * model -> form
     * @see DataTransformerInterface::transform()
     */
    public function transform($value)
    {
        return '';
    }

    /**
     * form -> model
     * @see DataTransformerInterface::transform()
     */
    public function reverseTransform($file)
    {
        if (!$file instanceof UploadedFile) {
            throw new \InvalidArgumentException('Given parameter is not an uploaded file, this transformer cannot work with anything else.');
        }

        if (!$this->documentFactory->supports($file)) {
            throw new \InvalidArgumentException('Given file is not supported.');
        }

        $extension = $file->guessExtension();
        $fileName  = $this->documentFactory->makeName($file, $this->documentName);

        $physicalDoc = $file->move($this->documentDir, $fileName);

        $document = DocumentQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByName($fileName)
            ->filterByType($extension)
            ->filterByPath($physicalDoc->getRealPath())
            ->findOneOrCreate()
        ;

        return $document;
    }
}
