<?php

namespace EasyTask\Bundle\WorkflowBundle\Model;

use EasyTask\Bundle\WorkflowBundle\Model\om\BaseWorkflow;

class Workflow extends BaseWorkflow
{
    protected $currentNode;

    /**
     * search and return current node
     * @param  Pdo          $connection optionnal connection for transactions
     * @return WorkflowNode
     */
    public function getCurrentNode(\Pdo $connection = null)
    {
        if (!empty($this->currentNode)) {
            return $this->currentNode;
        }

        return $this->currentNode = $this->getWorkflowNodes(
            WorkflowNodeQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->filterByCurrent(true)
        )->getFirst();
    }
}
