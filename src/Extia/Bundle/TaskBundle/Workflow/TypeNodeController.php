<?php

namespace Extia\Bundle\TaskBundle\Workflow;

use Extia\Bundle\TaskBundle\Model\Task;
use Extia\Bundle\TaskBundle\Model\TaskQuery;

use EasyTask\Bundle\WorkflowBundle\Workflow\TypeNodeController as EasyTaskTypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;
use EasyTask\Bundle\WorkflowBundle\Model\WorkflowNode;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @param Task  $nextTask
     * @param Task  $prevTask   (optionnal previous task)
     * @param array $parameters optionnal parameters given to notify
     * @param Pdo   $connection optionnal database connection used to create nodes
     */
    protected function onTaskCreation(Task $nextTask, Task $prevTask = null, array $parameters = array(), \Pdo $connection = null) { }

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
    protected function endNotify(WorkflowNode $node, array $parameters = array(), \Pdo $connection = null)
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
            $user = $this->getUser(); // but can be null
            $workflowCreatedBy = empty($user) ? null : $user->getUsername();
        }

        $nextTask->setWorkflowCreatedBy($workflowCreatedBy);
        $nextTask->setAssignedTo($workflowCreatedBy);

        // launch hook
        $this->onTaskCreation($nextTask, $prevTask, $parameters, $connection);

        $nextTask->save($connection);

        return parent::endNotify($node, $parameters, $connection);
    }

    /**
     * find current task for given workflow
     *
     * @param  Workflow                 $workflow
     * @param  \Pdo                     $pdo
     * @return Task
     * @throws InvalidArgumentException if task not found
     */
    public function findCurrentTask(Workflow $workflow, \Pdo $pdo = null)
    {
        $task = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->joinWith('Node')
            ->joinWith('Node.Workflow')
            ->joinWith('UserTarget', \Criteria::LEFT_JOIN)

            ->useNodeQuery()
                ->filterByCurrent(true)
                ->filterByWorkflow($workflow)
            ->endUse()

            ->findOne($pdo);

        if (empty($task)) {
            throw new \InvalidArgumentException(sprintf('Any active task found for workflow "%s".',
                $workflow->getName()
            ));
        }

        return $task;
    }

    /**
     * redirect on _redirect_url request query param or on given url
     *
     * @param  string           $route
     * @param  array            $params
     * @return RedirectResponse
     */
    public function redirectOrDefault($route, $params = array())
    {
        return $this->redirect(
            $this->get('request')->query->get('_redirect_url',
                $this->get('router')->generate($route, $params)
            )
        );
    }

    /**
     * waypoint to adds parameters to all node templates
     *
     * @param  array $parameters
     * @return array
     */
    public function addTaskParams(Task $task, array $parameters = array())
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

        return array_replace_recursive($extraParameters, $parameters);
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
     * @param  Task     $task
     * @param  string   $template
     * @return Response
     */
    protected function executeNode(Request $request, Task $task, $template)
    {
        throw new \BadMethodCallException('This method has to be defined in children node task classes.');
    }

    /**
     * return template for given type is modal, node, timeline....
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
     * @param  Workflow $workflow
     * @return Response
     */
    public function nodeAction(Request $request, Workflow $workflow)
    {
        return $this->executeNode($request, $this->findCurrentTask($workflow), $this->getTemplate('node'));
    }

    /**
     * modal action - execution of current node and renderer as a modal
     *
     * @param  Request  $request
     * @param  Workflow $workflow
     * @return Response
     */
    public function modalAction(Request $request, Workflow $workflow)
    {
        return $this->executeNode($request, $this->findCurrentTask($workflow), $this->getTemplate('modal'));
    }

    /**
     * notification action - renders state of this node for notification
     *
     * @param  Request  $request
     * @param  Workflow $workflow
     * @return Response
     */
    public function notificationAction(Request $request, Task $task)
    {
        return $this->render(
            $this->getTemplate('notification'),
            $this->addTaskParams($task, array('task' => $task))
        );
    }

    /**
     * timeline action - renders state of this node as timeline
     *
     * @param  Request  $request
     * @param  Workflow $workflow
     * @return Response
     */
    public function timelineAction(Request $request, Task $task)
    {
        return $this->render(
            $this->getTemplate('timeline_element'),
            $this->addTaskParams($task, array('task' => $task))
        );
    }
}
