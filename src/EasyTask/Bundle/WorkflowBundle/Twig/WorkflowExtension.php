<?php

namespace EasyTask\Bundle\WorkflowBundle\Twig;

use EasyTask\Bundle\WorkflowBundle\Workflow\Aggregator;
use EasyTask\Bundle\WorkflowBundle\Model\WorkflowNode;

use Symfony\Component\Routing\RouterInterface;

/**
 * Twig extension for workflow management, like links...
 * @see EasyTask/Bundle/WorkflowBundle/Resources/config/templating.xml
 */
class WorkflowExtension extends \Twig_Extension
{
    protected $workflows;
    protected $router;

    /**
     * construct
     * @param Aggregator      $workflows
     * @param RouterInterface $router
     */
    public function __construct(Aggregator $workflows, RouterInterface $router)
    {
        $this->workflows = $workflows;
        $this->router    = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'workflow';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'node_link'   => new \Twig_Function_Method($this, 'handleNodeLink', array('is_safe' => array('html')))
        );
    }

    /**
     * returns link to given node
     * @param  WorkflowNode $node
     * @return string
     */
    public function handleNodeLink(WorkflowNode $node, array $params = array())
    {
        $nodeType = $this->workflows->getNode(
            $node->getWorkflow(), $node->getName()
        );

        return $this->router->generate(
            $nodeType->getRoute(), array_replace_recursive(
                array('Id' => $node->getWorkflowId()), $params
            )
        );
    }
}
