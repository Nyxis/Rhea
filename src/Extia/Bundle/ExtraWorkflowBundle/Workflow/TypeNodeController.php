<?php

namespace Extia\Bundle\ExtraWorkflowBundle\Workflow;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\Task;
use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\TaskQuery;

use EasyTask\Bundle\WorkflowBundle\Workflow\TypeNodeController as EasyTaskTypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow\WorkflowNode;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    protected function onTaskCreation(Request $request, Task $nextTask, Task $prevTask = null, \Pdo $connection = null) { }

    /**
     * {@inherit_doc}
     *
     * override to create Extia task in same time
     */
    protected function endNotify(WorkflowNode $node, Request $request, \Pdo $connection = null)
    {
        $prevTask = null;

        $nextTask = new Task();
        $nextTask->setNode($node);

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
        $this->onTaskCreation($request, $nextTask, $prevTask, $connection);

        $nextTask->save($connection);

        return parent::endNotify($node, $request, $connection);
    }

    /**
     * find requested task by it's id
     * @param  int                   $taskId
     * @return Task
     * @throws NotFoundHttpException if task not found
     */
    public function findTask($taskId)
    {
        $task = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWith('Node')
            ->joinWith('Node.Workflow')
            ->joinWith('UserTarget')
            ->findPk($taskId);

        if (empty($task)) {
            throw new \InvalidArgumentException(sprintf('Any task found for given id. "%s" given',
                $taskId
            ));
        }

        return $task;
    }

    /**
     * returns task and node for given id
     * @param  int                   $workflowId
     * @param  Task                  $task       optionnal task, if given stored and returned
     * @param  Pdo                   $con        db connection
     * @return Task
     * @throws NotFoundHttpException If workflow not found
     */
    public function findCurrentTaskByWorkflowId($workflowId, Task $task = null, \Pdo $con = null)
    {
        if (empty($workflowId) && empty($task)) {
            throw new \InvalidArgumentException(sprintf('Not enough parameter given to %s() method, you have to provide at least a workflowId or an instanciate Task',
                __METHOD__
            ));
        }

        if ($task instanceof Task) {
            $this->currentTask = $task;
        } else {
            $this->findWorkflowNode($workflowId, $con);
        }

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
            ->useNodeQuery()
                ->filterByName($this->name)
                ->filterByWorkflowId($workflowId)
                ->filterByCurrent(true)
            ->endUse()
            ->joinWith('Node')
            ->joinWith('Node.Workflow')
            ->findOne($con);

        if (empty($this->currentTask)) {
            throw new NotFoundHttpException(sprintf('Any active %s workflow node found for given workflow id (%s given)', $this->name, $workflowId));
        }

        return $this->currentTask->getNode();
    }

    /**
     * redirect on given route, with a task notification using session flashbag system
     * @param  string   $level  notification level (error, info, warning, success)
     * @param  Task     $task   current task
     * @param  string   $route
     * @param  array    $params optionnal route params
     * @return Response
     */
    public function redirectWithNodeNotification($level, Task $task, $route, $params = array())
    {
        $nodeType = $task->getNode()->getType();
        if ($nodeType->supportsAction('notify')) {
            $this->get('session')->getFlashbag()->add($level, array(
                'controller' => $nodeType->getAction('notify'),
                'params'     => array('taskId' => $task->getId())
            ));
        }

        $request = $this->get('request');

        return $this->redirect(
            $request->query->get('_redirect_url',
                $this->get('router')->generate($route, $params)
            )
        );
    }

    /**
     * override rendering params to auto injects params
     *
     * {@inherit_doc}
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        $extraParameters = array(
            'nodeUrlParams' => array()
        );

        $redirectUrl = $this->get('request')->get('_task_redirect_url');
        if (!empty($redirectUrl)) {
            $extraParameters['nodeUrlParams'] = array(
                '_redirect_url' => $redirectUrl
            );
        }

        return parent::render($view, array_replace_recursive($extraParameters, $parameters), $response);
    }

}
