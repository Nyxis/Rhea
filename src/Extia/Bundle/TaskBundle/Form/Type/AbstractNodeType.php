<?php

namespace Extia\Bundle\TaskBundle\Form\Type;

use Extia\Bundle\UserBundle\Model\ConsultantQuery;
use Extia\Bundle\DocumentBundle\Factory\DocumentFactoryInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Base class for node form types
 * @see Extia/Bundles/TaskBundle/Resources/config/services.xml
 */
abstract class AbstractNodeType extends AbstractType
{
    protected $documentFactory;
    protected $translator;
    protected $securityContext;

    /**
     * contructor
     * @param TranslatorInterface $translator
     * @param string              $documentRootDirectory
     */
    public function __construct(TranslatorInterface $translator, SecurityContextInterface $securityContext, DocumentFactoryInterface $documentFactory)
    {
        $this->translator      = $translator;
        $this->securityContext = $securityContext;
        $this->documentFactory = $documentFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        return $resolver->setDefaults(array(
            'document_name_model' => null,
            'document_directory'  => null
        ));
    }

    /**
     * returns document transformer
     * @param  array                       $options form type options
     * @return TaskDocumentDataTransformer
     */
    protected function createDocumentTransformer(array $options)
    {
        if (empty($options['document_name_model']) || empty($options['document_directory'])) {
            throw new \InvalidArgumentException(sprintf('Given options are not enougth, document transformer need at least you provide a value for "document_name_model" and "document_directory" options, "%s" and "%s" given.',
                $options['document_name_model'], $options['document_directory']
            ));
        }

        return $this->documentFactory->createDataTransformer(
            $options['document_name_model'],
            sprintf('%s/%s',
                $this->documentFactory->getDirectory(),
                $options['document_directory']
            )
        );
    }

    /**
     * construct a choice list for all consultants
     * @return array
     */
    protected function getConsultantsChoices()
    {
        $consultants = ConsultantQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->orderByLastname()
            ->orderByFirstname()
            ->find();

        return $consultants->toKeyValue('Id', 'LongName');
    }
}
