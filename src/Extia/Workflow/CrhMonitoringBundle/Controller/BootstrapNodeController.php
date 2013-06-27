<?php

namespace Extia\Workflow\CrhMonitoringBundle\Controller;

use Extia\Bundle\ExtraWorkflowBundle\Workflow\TypeNodeController;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * bootstrap workflow node controller
 * @see Extia\Bundle\ExtraWorkflowBundle\Workflow\TypeNodeController
 */
class BootstrapNodeController extends TypeNodeController
{
    public function nodeAction(Request $request, $workflowId)
    {
        $error = '';
        $task  = $this->findTask($workflowId);
        $form  = $this->get('crh_monitoring.bootstrap.form');

        if ($request->request->has($form->getName())) {

            $handler  = $this->get('crh_monitoring.bootstrap.handler');
            $response = $handler->handle($form, $request, $task);

            if ($response instanceof Response) {
                return $response;
            }

            $error = $handler->error;
        }

        return $this->render('ExtiaWorkflowCrhMonitoringBundle:Bootstrap:node.html.twig', array(
            'error' => $error,
            'task'  => $task,
            'form'  => $form->createView()
        ));
    }
}
