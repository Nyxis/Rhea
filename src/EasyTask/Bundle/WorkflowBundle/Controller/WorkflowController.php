<?php

namespace EasyTask\Bundle\WorkflowBundle\Controller;

use EasyTask\Bundle\WorkflowBundle\Model\WorkflowQuery;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * workflow controller
 */
class WorkflowController extends Controller
{
    /**
     * action which displays new workflow form
     * @param Request $request
     */
    public function formAction(Request $request)
    {
        $wfId = $request->get('workflow_id');
        if ($wfId) {
            $wf = WorkflowQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->findPk($wfId);
            if (!$wf) {
                throw new NotFoundHttpException('Any workflow found for given id : '.$wfId);
            }
        } else {
            $wf = new Workflow();
        }

        $form = $this->container->get('form.factory')->create('workflow_creation_form', $wf, array());

        if ($request->request->has($form->getName())) {
            if ($response = $this->get('easy_task_workflow.new_workflow_handler')->handle($form, $request)) {
                return $response;
            }
        }

        return $this->render('EasyTaskWorkflowBundle:Workflow:form.html.twig', array(
            'workflow' => $wf,
            'form'     => $form->createView()
        ));
    }
}
