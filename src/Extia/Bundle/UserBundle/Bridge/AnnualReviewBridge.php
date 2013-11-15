<?php

namespace Extia\Bundle\UserBundle\Bridge;

use Extia\Bundle\UserBundle\Model\Consultant;

/**
 * bridge to annual review bundle
 *
 * @see Extia/Bundles/UserBundle/Resources/config/bridges.xml
 */
class AnnualReviewBridge extends AbstractTaskBridge
{
    /**
     * @see AbstractTaskBridge::getBridgedWorkflow()
     */
    protected function getBridgedWorkflow()
    {
        return 'annual_review';
    }

    /**
     * creates and init annual review for given consultant
     *
     * @param Consultant $consultant
     * @param int        $nextDate   next meeting date (timestamp) - optionnal
     * @param \Pdo       $pdo
     */
    public function createReview(Consultant $consultant, $nextDate = null, \Pdo $pdo = null)
    {
        $currentTask  = $this->createWorkflow(array(), $pdo);

        // bootstrap task
        return $this->resolveNode($currentTask, array(

                // workflow data
                'workflow' => array(
                    'name' => $this->translator->trans('annual_review.default_name', array(
                        '%user_target%' => $consultant->getLongName()
                    )),
                    'description' => $this->translator->trans('annual_review.default_desc', array(
                        '%user_target%' => $consultant->getLongName()
                    ))
                ),

                // bootstrap data
                'user_target_id' => $consultant->getId(),
                'assigned_to'    => $consultant->getCrh($pdo),
                'next_date'      => $currentTask->findNextWorkingDay(empty($nextDate) ?
                    (int) $currentTask->calculateDate($consultant->getContractBeginDate(), '+1 year', 'U') :
                    $nextDate
                )
            ),
            $pdo
        );
    }
}
