<?php

namespace Extia\Bundle\TaskBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\Task;

use EasyTask\Bundle\WorkflowBundle\Workflow\Aggregator;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
/**
 * Base class for node handlers
 * @see Extia/Bundles/TaskBundle/Resources/config/services.xml
 */
abstract class AbstractNodeHandler
{
    public $error = '';

    /**
     * @var Aggregator
     */
    protected $workfows;

    /**
     * set workflows list
     * @param Aggregator $workflows [description]
     */
    public function setWorkflows(Aggregator $workflows)
    {
        $this->workflows = $workflows;
    }

    /**
     * form handling method
     * @param  Form     $form
     * @param  Request  $request
     * @param  Task     $task
     * @return Response | null
     */
    public function handle(Form $form, Request $request, Task $task)
    {
        $form->submit($request);
        if (!$form->isValid()) {
            return false;
        }

        // updates task with incomming data
        return $this->resolve(
            $form->getData(), $task, $request
        );
    }

    /**
     * notify task given node name as next node
     * @param  string        $nodeName
     * @param  Task          $task
     * @param  Request       $request
     * @return Response|null
     */
    public function notifyNext($nodeName, Task $task, Request $request, \Pdo $pdo = null)
    {
        $workflow = $task->getNode()->getWorkflow();

        return $this->workflows
            ->getNode($workflow, $nodeName)
            ->notify($workflow, $request, $pdo);
    }

    /**
     * node handling method
     * @param  array    $data
     * @param  Request  $request
     * @param  Task     $task
     * @return Response | null
     */
    abstract public function resolve(array $data, Task $task, Request $request, \Pdo $pdo = null);

    /**
     * updates task linked workflow if workflow data given
     * @param array $data
     * @param Task  $task
     * @param Pdo   $con  optionnal db connection
     */
    protected function updateWorkflow(array $data, Task $task, \Pdo $con = null)
    {
        if (empty($data['workflow'])) {
            return;
        }

        $workflow = $task->getNode($con)->getWorkflow($con);

        if (isset($data['workflow']['name'])) {
            $workflow->setName($data['workflow']['name']);
        }
        if (isset($data['workflow']['description'])) {
            $workflow->setDescription($data['workflow']['description']);
        }

        $workflow->save($con);
    }
}
