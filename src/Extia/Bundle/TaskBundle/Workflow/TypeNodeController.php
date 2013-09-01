<?php

namespace Extia\Bundle\TaskBundle\Workflow;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Model\TaskQuery;

use EasyTask\Bundle\WorkflowBundle\Workflow\TypeNodeController as EasyTaskTypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;
use EasyTask\Bundle\WorkflowBundle\Model\WorkflowNode;

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
     * hook method called after changing an active node task activation date
     * @param Task $task
     */
    public function onTaskDiffering(Task $task)
    {
        $task->defineCompletionDate('+1 day');
    }

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
            $this->get('notifier')->add(
                $level,
                $nodeType->getAction('notify'),
                array('taskId' => $task->getId()),
                'controller'
            );
        }

        return $this->redirect(
            $this->get('request')->query->get('_redirect_url',
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


    // -----------------------------------------------
    // Mutualized tasks actions (override for custom)
    // -----------------------------------------------

    /**
     * has to return task type form handler
     * @return AbstractNodeHandler
     */
    public function getHandler()
    {
        throw new \BadMethodCallException('This method has to be defined in children node task classes.');
    }

    /**
     * has to return templates for node actions
     * @example
     *     return array(
     *         'node' => 'MyVendorWorkflowBundle:Node:node.html.twig'
     *     );
     *
     * @return array
     */
    protected function getTemplates()
    {
        throw new \BadMethodCallException('This method has to be defined in children node task classes.');
    }

    /**
     * has to return execute given node task, and return a response
     * @param  Request  $request
     * @param  int      $workflowId
     * @param  Task     $task
     * @param  string   $template
     * @return Response
     * @return array
     */
    protected function executeNode(Request $request, $workflowId = null, Task $task = null, $template = '')
    {
        throw new \BadMethodCallException('This method has to be defined in children node task classes.');
    }

    /**
     * return template for given typeis modal, node, timeline....
     * @param  string $type
     * @return string
     */
    private function getTemplate($type)
    {
        $templates = $this->getTemplates();

        return empty($templates[$type]) ?
            'ExtiaTaskBundle:Workflow:'.$type.'.html.twig' :
            $templates[$type];
    }

    /**
     * node action - execution of current node
     *
     * @param  Request  $request
     * @param  int      $workflowId
     * @param  Task     $task
     * @return Response
     */
    public function nodeAction(Request $request, $workflowId = null, Task $task = null)
    {
        return $this->executeNode($request, $workflowId, $task, $this->getTemplate('node'));
    }

    /**
     * modal action - execution of current node and renderer as a modal
     *
     * @param  Request  $request
     * @param  int      $workflowId
     * @param  Task     $task
     * @return Response
     */
    public function modalAction(Request $request, $workflowId = null, Task $task = null)
    {
        return $this->executeNode($request, $workflowId, $task, $this->getTemplate('modal'));
    }

    /**
     * notification action - renders state of this node for notification
     *
     * @param  Request  $request
     * @param  int      $workflowId
     * @return Response
     */
    public function notificationAction(Request $request, $taskId)
    {
        return $this->render($this->getTemplate('notification'), array(
            'task' => $this->findTask($taskId)
        ));
    }

    /**
     * timeline action - renders state of this node as timeline
     *
     * @param  Request  $request
     * @param  int      $taskId
     * @return Response
     */
    public function timelineAction(Request $request, $taskId, $params = array())
    {
        return $this->render($this->getTemplate('timeline_element'),
            array_replace_recursive($params, array('task' => $this->findTask($taskId)))
        );
    }
}
