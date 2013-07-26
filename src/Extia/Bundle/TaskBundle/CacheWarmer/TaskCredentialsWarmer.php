<?php

namespace Extia\Bundle\TaskBundle\CacheWarmer;

use Extia\Bundle\UserBundle\Model\CredentialQuery;

use EasyTask\Bundle\WorkflowBundle\Workflow\Aggregator;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

/**
 * Warmer to auto insert if not exists task credentials into db
 * @see Extia/Bundle/TaskBundle/Resources/config/services.xml
 */
class TaskCredentialsWarmer implements CacheWarmerInterface
{
    protected $workflows;
    protected $translator;
    protected $managedLocales;
    protected $managedTasksAccess = array('read', 'write');

    /**
     * construct
     * @param Aggregator          $workflows
     * @param TranslatorInterface $translator
     */
    public function __construct(Aggregator $workflows, TranslatorInterface $translator, array $managedLocales)
    {
        $this->workflows      = $workflows;
        $this->translator     = $translator;
        $this->managedLocales = $managedLocales;
    }

    /**
     * @see CacheWarmerInterface::isOptionnal()
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->workflows->all() as $wfName => $typeWorkflow) {
            foreach ($this->managedTasksAccess as $taskAccess) {
                $credential = CredentialQuery::create()
                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                    ->filterByType('task')
                    ->filterByCode(sprintf('workflow_%s_%s', $wfName, $taskAccess))
                    ->findOneOrCreate();

                foreach ($this->managedLocales as $locale) {
                    $credential->getTranslation($locale)
                        ->setLabel($this->translator->trans(sprintf('%s_credential.%s',
                            $wfName, $taskAccess
                        )));
                }

                $credential->save();
            }
        }
    }

    /**
     * @see CacheWarmerInterface::isOptionnal()
     */
    public function isOptional()
    {
        return true;
    }
}
