<?php

namespace EasyTask\Bundle\WorkflowBundle\Form\Handler;

use EasyTask\Bundle\WorkflowBundle\Workflow\Aggregator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormInterface;

/**
 * workflow creation handler
 * @see EasyTask/Bundle/WorkflowBundle/Resources/config/forms.xml
 */
class NewWorkflowHandler
{
    protected $aggregator;

    /**
     * construct
     * @param Aggregator $aggregator
     */
    public function __construct(Aggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    /**
     * handle a workflow creation form and boot it
     * @param  FormInterface $form
     * @return Response|null
     */
    public function handle(FormInterface $form, Request $request)
    {
        $form->submit($request);
        if (!$form->isValid()) {
            return false;
        }

        $connection = \Propel::getConnection('default');
        $connection->beginTransaction();

        try {
            $return = $this->aggregator->boot($form->getData(), array(), $connection);

            $connection->commit();

            return $return;
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }
}
