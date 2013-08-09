<?php

namespace Extia\Bundle\TaskBundle\Form\DataTransformer;

use Extia\Bundle\DocumentBundle\Model\DocumentQuery;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for files into documents
 */
class TaskDocumentDataTransformer implements DataTransformerInterface
{
    protected $documentDir;
    protected $documentName;

    /**
     * construct with base document path in filesystem
     * @param string $documentName
     * @param string $documentDir
     */
    public function __construct($documentName, $documentDir)
    {
        $this->documentName = $documentName;
        $this->documentDir  = $documentDir;
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

        $extension = $file->guessExtension();
        $fileName  = sprintf('%s_%s.%s',
            date('Y_m_d'),
            $this->documentName,
            $extension
        );

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
