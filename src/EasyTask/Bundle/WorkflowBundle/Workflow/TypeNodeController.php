<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;
use EasyTask\Bundle\WorkflowBundle\Model\WorkflowNode;
use EasyTask\Bundle\WorkflowBundle\Model\WorkflowNodeQuery;
use EasyTask\Bundle\WorkflowBundle\Event\NodeEvent;
use EasyTask\Bundle\WorkflowBundle\Event\WorkflowEvents;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Type Node class
 * Copied into service for each defined nodes
 */
class TypeNodeController extends Controller implements TypeNodeControllerInterface
{
    protected $name;
    protected $routeNode;
    protected $actions;

    /**
     * @see TypeNodeControllerInterface:setRoute()
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    /**
     * @see TypeNodeControllerInterface:getName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @see TypeNodeControllerInterface:setRoute()
     */
    public function setRoute($route)
    {
        $this->routeNode = $route;
    }

    /**
     * @see TypeNodeControllerInterface:getRoute()
     */
    public function getRoute()
    {
        return $this->routeNode;
    }

    /**
     * @see TypeNodeControllerInterface:registerActions()
     */
    public function registerActions(array $actions)
    {
        $this->actions = $actions;
    }

    /**
     * @see TypeNodeControllerInterface:supportsAction()
     */
    public function supportsAction($actionName)
    {
        return !empty($this->actions[$actionName]);
    }

    /**
     * @see TypeNodeControllerInterface:getAction()
     */
    public function getAction($actionName)
    {
        if (!$this->supportsAction($actionName)) {
            throw new \InvalidArgumentException(sprintf('Given action is not supported by %s node.', $this->name))            ;
        }

        return $this->actions[$actionName];
    }

    /**
     * @see TypeNodeControllerInterface:notify()
     */
    public function notify(Workflow $workflow, array $parameters = array(), \Pdo $connection = null)
    {
        $newNode = $this->beginNotify($workflow, $parameters, $connection);
        if (empty($newNode)) { // no custom node used here

            // setup new
            $newNode = new WorkflowNode();
            $newNode->setWorkflow($workflow);
            $newNode->setName($this->name);

            // updates old node
            $oldNode = $workflow->getCurrentNode($connection);

            // hook method
            $this->onNodeCreation($newNode, $oldNode, $parameters, $connection);

            if (!empty($oldNode)) {
                $newNode->setPrevId($oldNode->getId());
            }

            $newNode->setCurrent(true);

            $nodeEvent = new NodeEvent($newNode, $workflow, $connection);
            $this->get('event_dispatcher')->dispatch(WorkflowEvents::WF_NODE_ACTIVATION, $nodeEvent);

            $newNode->save($connection);

            if (!empty($oldNode)) {
                $oldNode->setCurrent(false);
                $oldNode->setNextId($newNode->getId());
                $oldNode->setCompletedAt(time());

                $nodeEvent = new NodeEvent($oldNode, $workflow, $connection);
                $this->get('event_dispatcher')->dispatch(WorkflowEvents::WF_NODE_SHUTDOWN, $nodeEvent);

                $oldNode->save($connection);
            }
        }

        $this->endNotify($newNode, $parameters, $connection);

        return $this->renderNotify($newNode, $parameters);
    }

    /**
     * renders response, by default redirects on next node if web context, true otherwise
     *
     * @param  WorkflowNode       $node new node
     * @return Response|true|null
     */
    protected function renderNotify(WorkflowNode $node, array $parameters = array())
    {
        if (!$this->container->isScopeActive('request')) {
            return true;
        }

        if (empty($this->routeNode)) {
            throw new \RuntimeException(sprintf('Without define "route" config on "%s" node, we cannot know where redirect on.
                You have to define your own notify(), or endNotify() method into a custom controller using "class" key;
                or define a "route" key otherwise.', $this->name));
        }

        return $this->redirect($this->get('router')->generate($this->routeNode, array(
            'Id' => $node->getWorkflowId()
        )));
    }

    /**
     * empty method called before node switching, and after new node creation to custom
     * both nodes if needed
     *
     * @param WorkflowNode $newNode
     * @param WorkflowNode $oldNode    (null if current node is bootstrap)
     * @param Pdo          $connection optionnal database connection used to create nodes
     */
    protected function onNodeCreation(WorkflowNode $newNode, WorkflowNode $oldNode = null, array $parameters = array(), \Pdo $connection = null) { }

    /**
     * hook method to handle post notification
     * if method returns a WorkflowNode, notify will use it instead of creating a new one
     *
     * @param Workflow $workflow   current workflow
     * @param Pdo      $connection optionnal database connection used to create nodes
     */
    protected function beginNotify(Workflow $workflow, array $parameters = array(), \Pdo $connection = null) { }

    /**
     * hook method to handle end notification
     *
     * @param Workflow $workflow   current workflow
     * @param Request  $request
     * @param Pdo      $connection optionnal database connection used to create nodes
     */
    protected function endNotify(WorkflowNode $node, array $parameters = array(), \Pdo $connection = null) { }

    /**
     * returns node and workflow for given id
     * @param  int                   $workflowId
     * @param  Pdo                   $con        db connection
     * @return Workflow
     * @throws NotFoundHttpException If workflow not found
     */
    public function findWorkflowNode($workflowId, \Pdo $con = null)
    {
        $node = WorkflowNodeQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByName($this->name)
            ->filterByWorkflowId($workflowId)
            ->filterByCurrent(true)
            ->joinWith('Workflow')
            ->findOne($con);

        if (empty($node)) {
            throw new NotFoundHttpException(sprintf('Any %s workflow node found for given workflow id (%s given)', $this->name, $workflowId));
        }

        return $node;
    }
}
