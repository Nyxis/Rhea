<?php

namespace Extia\Bundle\TaskBundle\Controller;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use Extia\Bundle\DocumentBundle\Model\DocumentQuery;

use EasyTask\Bundle\WorkflowBundle\Model\WorkflowQuery;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * controller for tasks components
 */
class TaskController extends Controller
{
    /**
     * action for workflow details, displays a timeline for
     * given workflow id
     *
     * @param  Request  $request
     * @return Response
     */
    public function workflowHistoryAction(Request $request)
    {
        // find instead of findPk to use join with, and perform always one request
        $tasks = TaskQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))

            ->useNodeQuery()
                ->useWorkflowQuery()
                    ->filterById($request->attributes->get('workflow_id'))
                    ->filterByType(array_keys($this->get('workflows')->getAllowed('read')))
                ->endUse()
                ->orderByCurrent(\Criteria::DESC)
                ->orderByCompletedAt(\Criteria::DESC)
            ->endUse()

            ->joinWithAll()

            ->find();

        if ($tasks->isEmpty()) {
            throw new NotFoundHttpException(sprintf('Any tasks found for given workflow id : "%s"',
                $request->attributes->get('workflow_id')
            ));
        }

        $workflow = $tasks->getFirst()->getNode()->getWorkflow();
        $form     = $this->get('form.factory')->create('workflow_data', $workflow);

        return $this->render('ExtiaTaskBundle:Task:workflow_detail.html.twig', array(
            'workflow' => $workflow,
            'tasks'    => $tasks,
            'form'     => $form->createView()
        ));
    }

    /**
     * edits given workflow with incomming posted form
     * @param  Request  $request
     * @param  int      $workflowId
     * @return Response
     */
    public function workflowEditAction(Request $request, $workflow_id)
    {
        $workflow = WorkflowQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->filterByType(array_keys($this->get('workflows')->getAllowed('write')))
            ->findPk($workflow_id);

        if (empty($workflow)) {
            throw new \NotFoundHttpException(sprintf('Given workflow id is unknown : "%s" given', $workflow_id));
        }

        $form = $this->get('form.factory')->create('workflow_data', $workflow);
        $form->submit($request);
        if ($form->isValid()) {
            $workflow->save();
        } else {
            $this->get('session')->getFlashbag()->add('error', array(
                'message' => 'workflow.notification.edit_form_invalid'
            ));
        }

        return $this->redirect($request->get('redirect_url',
            $this->get('router')->generate('Rhea_homepage')
        ));
    }

    /**
     * differs given task with a message
     * @param  Request  $request
     * @return Response
     */
    public function differAction(Request $request)
    {
        $form = $this->get('extia.task.form.differ');

        if ($request->request->has($form->getName())) {
            $this->get('extia.task.form.differ_handler')->handle($form, $request);

            return $this->redirect($request->query->get('redirect_url',
                $this->get('router')->generate('Rhea_homepage')
            ));
        }

        return $this->render('ExtiaTaskBundle:Task:differ.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * displays given workflow documents
     * @param  Request  $request
     * @param  int      $workflowId
     * @return Response
     */
    public function workflowDocumentsAction(Request $request, $workflowId)
    {
        $documentsCollection = DocumentQuery::create()
            ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
            ->orderByCreatedAt(\Criteria::DESC)
            ->usePersonTaskDocumentQuery()
                ->useTaskQuery()
                    ->useNodeQuery()
                        ->filterByWorkflowId($workflowId)
                    ->endUse()
                ->endUse()
            ->endUse()
            ->find();

        return $this->render('ExtiaTaskBundle:Task:workflow_documents.html.twig', array(
            'documents' => $documentsCollection
        ));
    }
}
