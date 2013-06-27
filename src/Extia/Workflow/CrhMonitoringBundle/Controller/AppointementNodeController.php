<?php

namespace Extia\Workflow\CrhMonitoringBundle\Controller;

use Extia\Bundle\ExtraWorkflowBundle\Model\Workflow\Task;
use Extia\Bundle\ExtraWorkflowBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * appointement workflow node controller
 * @see Extia\Bundle\ExtraWorkflowBundle\Workflow\TypeNodeController
 */
class AppointementNodeController extends TypeNodeController
{
    /**
     * use hook method to adds prev task data into new
     *
     * {@inherit_doc}
     */
    protected function onTaskCreation(Request $request, Task $nextTask, Task $prevTask = null, \Pdo $connection = null)
    {
        $nextTask->setUserTargetId($prevTask->getUserTargetId());

        $oldData = $prevTask->getData();
        $nextTask->setActivationDate($oldData['next_date']);

        return parent::onTaskCreation($request, $nextTask, $prevTask, $connection);
    }

    /**
     * node action - execution of current node
     *
     * @param  Request  $request
     * @param  int      $workflowId
     * @return Response
     */
    public function nodeAction(Request $request, $workflowId)
    {
        $error = '';
        $task  = $this->findTask($workflowId);
        $form  = $this->get('crh_monitoring.appointement.form');

        if ($request->request->has($form->getName())) {

            $handler  = $this->get('crh_monitoring.appointement.handler');
            $response = $handler->handle($form, $request, $task);

            if ($response instanceof Response) {
                return $response;
            }

            $error = $handler->error;
        }

        return $this->render('ExtiaWorkflowCrhMonitoringBundle:Appointement:node.html.twig', array(
            'error' => $error,
            'task'  => $task,
            'form'  => $form->createView()
        ));
    }
}
