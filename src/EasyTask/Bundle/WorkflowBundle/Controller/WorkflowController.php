<?php

namespace EasyTask\Bundle\WorkflowBundle\Controller;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow\WorkflowQuery;
use EasyTask\Bundle\WorkflowBundle\Model\Workflow\Workflow;

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
            $wf = WorkflowQuery::create()->findPk($wfId);
            if (!$wf) {
                throw new NotFoundHttpException('Any workflow found for given id : '.$wfId);
            }
        } else {
            $wf = new Workflow();
        }

        $form = $this->container->get('form.factory')->create('workflow_form', $wf, array());

        if ($request->request->has($form->getName())) {
            $form->bind($request);
            if ($form->isValid()) {
                $response = $this->container->get('workflows')->handle($form, $request);

                if ($response instanceof Response) {
                    return $response;
                }
            }
        }

        return $this->render('EasyTaskWorkflowBundle:Workflow:form.html.twig', array(
            'workflow' => $wf,
            'form'     => $form->createView()
        ));
    }
}
