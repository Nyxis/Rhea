<?php

namespace Extia\Bundle\UserBundle\Bridge;

use Extia\Bundle\UserBundle\Model\Consultant;

/**
 * bridge to crh monitoring bundle
 *
 * @see Extia/Bundles/UserBundle/Resources/config/bridges.xml
 */
class CrhMonitoringBridge extends AbstractTaskBridge
{
    /**
     * @see AbstractTaskBridge::getBridgedWorkflow()
     */
    protected function getBridgedWorkflow()
    {
        return 'crh_monitoring';
    }

    /**
     * creates and init mission monitoring for given consultant
     *
     * @param Consultant $consultant
     * @param int        $nextDate   next meeting date (timestamp) - optionnal
     * @param \Pdo       $pdo
     */
    public function createMonitoring(Consultant $consultant, $nextDate = null, \Pdo $pdo = null)
    {
        $currentTask  = $this->createWorkflow(array(), $pdo);

        // bootstrap task
        return $this->resolveNode($currentTask, array(

                // workflow data
                'workflow' => array(
                    'name' => $this->translator->trans('crh_monitoring.default_name', array(
                        '%user_target%' => $consultant->getLongName()
                    )),
                    'description' => $this->translator->trans('crh_monitoring.default_desc', array(
                        '%user_target%' => $consultant->getLongName()
                    ))
                ),

                // bootstrap data
                'user_target_id' => $consultant->getId(),
                'assigned_to'    => $consultant->getCrh($pdo),
                'next_date'      => empty($nextDate) ?
                    $this->temporalTools->changeDate($consultant->getContractBeginDate(), '+1 month') :
                    $nextDate
            ),
            $pdo
        );
    }
}
