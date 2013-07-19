<?php

namespace Extia\Bundle\TaskBundle\Form\Type;

use Extia\Bundle\UserBundle\Model\ConsultantQuery;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Base class for node form types
 * @see Extia/Bundles/TaskBundle/Resources/config/services.xml
 */
abstract class AbstractNodeType extends AbstractType
{
    protected $translator;

    /**
     * contructor
     * @param TranslatorInterface $translator [description]
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
