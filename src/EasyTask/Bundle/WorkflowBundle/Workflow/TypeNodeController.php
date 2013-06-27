<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow\WorkflowNode;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow\WorkflowNodeQuery;
use EasyTask\Bundle\WorkflowBundle\Event\NodeEvent;
use EasyTask\Bundle\WorkflowBundle\Event\WorkflowEvents;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Type Node class
 * Copied into service for each defined nodes
 */
class TypeNodeController extends Controller implements TypeNodeControllerInterface
{
    protected $routeNode;
    protected $name;

    /**
     * @see TypeNodeControllerInterface:setRoute()
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @see TypeNodeControllerInterface:setRoute()
     */
    public function setRoute($route)
    {
        $this->routeNode = $route;
    }

    /**
     * @see TypeNodeControllerInterface:notify()
     */
    public function notify(Workflow $workflow, Request $request, \Pdo $connection = null)
    {
        $newNode = $this->beginNotify($workflow, $request, $connection);
        if (empty($newNode)) { // no custom node used here

            // setup new
            $newNode = new WorkflowNode();
            $newNode->setWorkflow($workflow);
            $newNode->setName($this->name);

            // updates old node
            $oldNode = $workflow->getCurrentNode($connection);

            // hook method
            $this->onNodeCreation($newNode, $oldNode, $request, $connection);

            if (!empty($oldNode)) {
                $newNode->setPrevId($oldNode->getId());
            }

            $newNode->setCurrent(true);

            $nodeEvent = new NodeEvent($newNode, $workflow, $request, $connection);
            $this->get('event_dispatcher')->dispatch(WorkflowEvents::WF_NODE_ACTIVATION, $nodeEvent);

            $newNode->save($connection);

            if (!empty($oldNode)) {
                $oldNode->setCurrent(false);
                $oldNode->setNextId($newNode->getId());

                $nodeEvent = new NodeEvent($oldNode, $workflow, $request, $connection);
                $this->get('event_dispatcher')->dispatch(WorkflowEvents::WF_NODE_SHUTDOWN, $nodeEvent);

                $oldNode->save($connection);
            }
        }

        $this->endNotify($newNode, $request, $connection);

        return $this->renderNotify($newNode, $request);
    }

    /**
     * renders response, by default redirects on next node
     *
     * @param  WorkflowNode  $node    new node
     * @param  Request       $request
     * @return Response|null
     */
    protected function renderNotify(WorkflowNode $node, Request $request)
    {
        if (empty($this->routeNode)) {
            throw new \RuntimeException(sprintf('Without define "route" config on "%s" node, we cannot know where redirect on.
                You have to define your own notify(), or endNotify() method into a custom controller using "class" key;
                or define a "route" key otherwise.', $this->name));
        }

        return $this->redirect($this->get('router')->generate($this->routeNode, array(
            'workflowId' => $node->getWorkflowId()
        )));
    }

    /**
     * empty method called before node switching, and after new node creation to custom
     * both nodes if needed
     *
     * @param WorkflowNode $newNode
     * @param WorkflowNode $oldNode    (null if current node is bootstrap)
     * @param Request      $request
     * @param Pdo          $connection optionnal database connection used to create nodes
     */
    protected function onNodeCreation(WorkflowNode $newNode, WorkflowNode $oldNode = null, Request $request, \Pdo $connection = null) { }

    /**
     * hook method to handle post notification
     * if method returns a WorkflowNode, notify will use it instead of creating a new one
     *
     * @param Workflow $workflow   current workflow
     * @param Request  $request
     * @param Pdo      $connection optionnal database connection used to create nodes
     */
    protected function beginNotify(Workflow $workflow, Request $request, \Pdo $connection = null) { }

    /**
     * hook method to handle end notification
     *
     * @param Workflow $workflow   current workflow
     * @param Request  $request
     * @param Pdo      $connection optionnal database connection used to create nodes
     */
    protected function endNotify(WorkflowNode $node, Request $request, \Pdo $connection = null) { }

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
