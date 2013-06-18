<?php

namespace EasyTask\Bundle\WorkflowBundle\Workflow;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow\WorkflowNode;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow\WorkflowNodeQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
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
    public function notify(Workflow $workflow, \Pdo $connection = null)
    {
        $newNode = $this->beginNotify($workflow);
        if (empty($newNode)) { // no custom node used here

            // setup new
            $newNode = new WorkflowNode();
            $newNode->setWorkflow($workflow);
            $newNode->setName($this->name);
            $newNode->setAssignedTo($workflow->getCreatedBy());

            // updates old node
            $oldNode = $workflow->getCurrentNode($connection);

            // hook method
            $this->customNode($newNode, $oldNode);

            if (!empty($oldNode)) {
                $newNode->setPrevId($oldNode->getId());
                $newNode->setAssignedTo($oldNode->getAssignedTo());
            }

            $newNode->setCurrent(true);
            $newNode->save($connection);

            if (!empty($oldNode)) {
                $oldNode->setCurrent(false);
                $oldNode->setNextId($newNode->getId());
                $oldNode->save($connection);
            }
        }

        $response = $this->endNotify($newNode);
        if ($response instanceof Response) { // custom response given

            return $response;
        }

        if (empty($this->routeNode)) {
            throw new \RuntimeException(sprintf('Without define "route" config on "%s" node, we cannot know where redirect on.
                You have to define your own notify(), or endNotify() method into a custom controller using "class" key;
                or define a "route" key otherwise.', $this->name));
        }

        return $this->redirect($this->get('router')->generate($this->routeNode, array(
            'workflowId' => $workflow->getId()
        )));
    }

    /**
     * empty method called before node switching, and after new node creation to custom
     * both nodes if needed
     *
     * @param WorkflowNode $newNode
     * @param WorkflowNode $oldNode (null if current node is bootstrap)
     */
    protected function customNode(WorkflowNode $newNode, WorkflowNode $oldNode = null) { }

    /**
     * hook method to handle post notification
     * if method returns a WorkflowNode, notify will use it instead of creating a new one
     *
     * @param Workflow $workflow current workflow
     */
    protected function beginNotify(Workflow $workflow) { }

    /**
     * hook method to handle end notification
     * if methods returns a Response, notify will return it, redirects on node route otherwise
     *
     * @param Workflow $workflow current workflow
     */
    protected function endNotify(WorkflowNode $node) { }

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
            ->joinWith('Workflow')
            ->findOne($con);

        if (empty($node)) {
            throw new NotFoundHttpException(sprintf('Any %s workflow node found for given workflow id (%s given)', $this->name, $workflowId));
        }

        return $node;
    }
}
