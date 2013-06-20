<?php

namespace Extia\Bundle\ExtraWorkflowBundle\Workflow;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\Task;
use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\TaskQuery;

use EasyTask\Bundle\WorkflowBundle\Workflow\TypeNodeController as EasyTaskTypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow\WorkflowNode;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Extia Type Node base class
 *
 */
class TypeNodeController extends EasyTaskTypeNodeController
{
    protected $currentTask;

    /**
     * hook method to allow to custom task before saving
     *
     * @param Request $request
     * @param Task    $nextTask
     * @param Task    $prevTask   (optionnal previous task)
     * @param Pdo     $connection optionnal database connection used to create nodes
     */
    protected function bindOnNotify(Request $request, Task $nextTask, Task $prevTask = null, \Pdo $connection = null) { }

    /**
     * {@inherit_doc}
     *
     * override to create Extia task in same time
     */
    protected function endNotify(WorkflowNode $node, Request $request, \Pdo $connection = null)
    {
        $prevTask = null;

        $nextTask = new Task();
        $nextTask->setWorkflowNode($node);

        if (!$node->isFirst()) {
            $prevTask = TaskQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->findOneByWorkflowNodeId($node->getPrevId(), $connection);

            $workflowCreatedBy = $prevTask->getWorkflowCreatedBy();
        } else {
            // workflow creator is always current if this node is first
            $workflowCreatedBy = $this->getUser()->getUsername();
        }

        $nextTask->setWorkflowCreatedBy($workflowCreatedBy);
        $nextTask->setAssignedTo($workflowCreatedBy);

        // launch hook
        $this->bindOnNotify($request, $nextTask, $prevTask, $connection);

        $nextTask->save($connection);

        return parent::endNotify($node, $request, $connection);
    }

    /**
     * returns task and node for given id
     * @param  int                   $workflowId
     * @param  Pdo                   $con        db connection
     * @return Task
     * @throws NotFoundHttpException If workflow not found
     */
    public function findTask($workflowId, \Pdo $con = null)
    {
        $this->findWorkflowNode($workflowId, $con);

        return $this->currentTask;
    }

    /**
     * overriden to loads task in same time
     * {@inherited_doc}
     */
    public function findWorkflowNode($workflowId, \Pdo $con = null)
    {
        $this->currentTask = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->useWorkflowNodeQuery()
                ->filterByName($this->name)
                ->filterByWorkflowId($workflowId)
            ->endUse()
            ->joinWith('WorkflowNode')
            ->joinWith('WorkflowNode.Workflow')
            ->findOne($con);

        if (empty($this->currentTask)) {
            throw new NotFoundHttpException(sprintf('Any %s workflow node found for given workflow id (%s given)', $this->name, $workflowId));
        }

        return $this->currentTask->getWorkflowNode();
    }

}
