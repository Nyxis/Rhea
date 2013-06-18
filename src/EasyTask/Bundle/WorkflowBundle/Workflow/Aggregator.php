<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;

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
    public function addWorkflow($wfName, TypeWorkflowInterface $workflowType)
    {
        return $this->set($wfName, $workflowType);
    }

    /**
     * return required node for given workflow
     *
     * @param  Workflow                 $workflow
     * @param  string                   $nodeName
     * @return WorkflowNode
     * @throws RuntimeException         if given workflow type is unknown
     * @throws InvalidArgumentException if workflow does not support given node name
     */
    public function getNode(Workflow $workflow, $nodeName)
    {
        $workflowType = $this->get($workflow->getType());
        if (empty($workflowType)) {
            throw new \RuntimeException(sprintf('Given workflow has an unsupported type : "%s". Check your configuration.',
                $workflow->getType()
            ));
        }

        $nodeType = $workflowType->getNode($nodeName);
        if (empty($nodeType)) {
            throw new \RuntimeException(sprintf('Unknow given node for workflow "%s" : "%s". Check your configuration.',
                $workflow->getType(),
                $nodeName
            ));
        }

        return $nodeType;
    }

    /**
     * handle a workflow creation form
     * @param  FormInterface $form
     * @param  Request       $request
     * @return Response|null
     */
    public function handle(FormInterface $form, Request $request)
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN')) {
            throw new \RuntimeException(sprintf('%s has not to be called without a firewall.', __METHOD__));
        }

        $wf    = $form->getData();
        $isNew = $wf->isNew();

        // injects connected user into task
        $user = $this->securityContext->getToken()->getUser();

        $connection = \Propel::getConnection('default');
        $connection->beginTransaction();

        try {

            //
            // /!\ TODO change when login will be implemented
            //
            // $wf->setCreatedBy($user->getId());
            $wf->setCreatedBy(\EasyTask\Bundle\UserBundle\Model\User\InternalQuery::create()->findOne()->getId());

            $wf->save($connection);

            // edit case : nothing more to do
            if (!$isNew) {
                return;
            }

            // boot workflow throught type service
            $return = $this->get($wf->getType())->boot($wf, $connection);

            $connection->commit();

            return $return;

        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }
}
