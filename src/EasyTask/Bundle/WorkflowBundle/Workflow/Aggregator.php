<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * workflow aggregator
 * @see WorkflowBundle/Resources/config/workflow.xml
 */
class Aggregator extends ParameterBag
{
    protected $securityContext;
    protected $session;
    protected $translator;

    /**
     * construct
     * @param SecurityContextInterface $securityContext
     * @param SessionInterface         $session
     */
    public function __construct(SecurityContextInterface $securityContext, SessionInterface $session, TranslatorInterface $translator)
    {
        $this->securityContext = $securityContext;
        $this->session         = $session;
        $this->translator      = $translator;
    }

    /**
     * method called by DI, dynamically from compiler pass
     * @see EasyTask\Bundle\WorkflowBundle\DependencyInjection\Compiler\WorkflowAggregatorCompilerPass
     */
    public function addWorkflow($wfName, TypeWorkflow $workflowType)
    {
        return $this->set($wfName, $workflowType);
    }

    /**
     * handle a workflow creation form
     * @param  FormInterface $form
     * @param  Request       $request
     * @return Response|null
     */
    public function handle(FormInterface $form, Request $request)
    {
        if (!$this->securityContext->isGranted('ROLE_INTERNAL')) {
            throw new \RuntimeException(sprintf('%s has not to be called without a firewall.', __METHOD__));
        }

        $wf    = $form->getData();
        $isNew = $wf->isNew();

        // injects connected user into task
        $user = $this->securityContext->getToken()->getUser();

        //
        // /!\ TODO change when login will be implemented
        //
        // $wf->setCreatedBy($user->getId());
        $wf->setCreatedBy(EasyTask\Bundle\UserBundle\Model\User\InternalQuery::create()->findOne()->getId());

        $wf->save();

        // edit case : nothing more to do
        if (!$isNew) {
            return;
        }

        // boot workflow throught type service
        return $this->get($wf->getType())->boot($wf);
    }
}
