<?php

namespace Extia\Bundle\TaskBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\TaskQuery;
use Extia\Bundle\TaskBundle\Workflow\Aggregator;

use Extia\Bundle\CommentBundle\Model\Comment;
use Extia\Bundle\NotificationBundle\Notification\NotifierInterface;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * handler for task differing
 * @see ExtiaTaskBundle/Resources/config/services/forms.xml
 */
class DifferTaskHandler
{
    protected $workflows;
    protected $securityContext;
    protected $translator;
    protected $notifier;

    /**
     * construct
     */
    public function __construct(Aggregator $workflows, SecurityContextInterface $securityContext, TranslatorInterface $translator, NotifierInterface $notifier)
    {
        $this->workflows       = $workflows;
        $this->securityContext = $securityContext;
        $this->translator      = $translator;
        $this->notifier        = $notifier;
    }

    /**
     * handles given form
     * @param  Form    $form
     * @param  Request $request
     * @return bool
     */
    public function handle(Form $form, Request $request)
    {
        $form->submit($request);

        if (!$form->isValid()) {
            $this->notifier->add('warning', 'task.differ.notification.invalid');

            return;
        }

        $data = $form->getData();

        try {
            $internal = $this->securityContext->getToken()->getUser();
            $teamIds  = $internal->getTeamIds();

            // me and my team
            if (empty($teamIds)) {
                $teamIds = array($internal->getId());
            } else {
                $teamIds->prepend($internal->getId());
                $teamIds = $teamIds->getData();
            }

            $task = TaskQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->_if(!$this->securityContext->isGranted('ROLE_ADMIN'))
                    ->filterByWorkflowTypes(array_keys($this->workflows->getAllowed('write')))
                    ->filterByAssignedTo($teamIds)
                ->_endif()
                ->findPk($data['task_id']);

            if (empty($task)) {
                $this->notifier->add('warning', 'task.differ.notification.invalid');

                return;
            }

            // modify date
            $oldDate = $task->getActivationDate();
            $task->setActivationDate($task->findNextWorkingDay($data['differ_date']));
            $task->getNode()->getType()->onTaskDiffering($task);

            // create comment
            $transParams = array(
                '%new_date%' => $task->getActivationDate($this->translator->trans('date_format.ymd')),
                '%old_date%' => $oldDate->format($this->translator->trans('date_format.ymd')),
                '%task%'     => $task->getNode()->getWorkflow()->getName()
            );

            $comment = new Comment();
            $comment->setWrittenBy($internal->getId());
            $comment->setTask($task);
            $comment->setText($data['comment']);
            $comment->setSysText($this->translator->trans('task.differ.comment.auto_message', $transParams));

            $task->save();

            $this->notifier->add('success', 'task.differ.notification.success', $transParams);

        } catch (\Exception $e) {
            $this->notifier->add('error', 'task.differ.notification.error');

            var_dump($e->getMessage());
            die;
        }
    }
}
