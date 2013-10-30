<?php

namespace Extia\Bundle\UserBundle\Domain;

use Extia\Bundle\UserBundle\Model\Consultant;

use Extia\Bundle\UserBundle\Bridge\CrhMonitoringBridge;
use Extia\Bundle\UserBundle\Bridge\AnnualReviewBridge;

/**
 * Consultant domain, repository of mission logic
 *
 * @see ExtiaUserBundle/Resources/config/domains.xml
 */
class ConsultantDomain
{
    protected $crhMonitoringBridge;
    protected $annualReviewBridge;

    /**
     * construct
     */
    public function __construct(CrhMonitoringBridge $crhMonitoringBridge, AnnualReviewBridge $annualReviewBridge)
    {
        $this->crhMonitoringBridge = $crhMonitoringBridge;
        $this->annualReviewBridge  = $annualReviewBridge;
    }

    /**
     * create a consultant from given array
     *
     * @param  array      $consultant
     * @return Consultant
     */
    public function createConsultant(array $consultant)
    {
        if (!$consultant instanceof Consultant) {
            $consultant = $this->createConsultantFromArray($consultant);
        }

        $consultant->setPersonTypeId(
            PersonTypeQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->select('Id')
                ->findOneByCode('clt')
        );
    }

    /**
     * register given consultant on workflow and missions, but not for now because time
     *
     * @param  Consultant    $consultant
     * @param  Mission|array $missions
     * @param  array         $options
     * @param  Pdo           $pdo
     * @return
     */
    public function registerConsultant(Consultant $consultant, $missions, array $options = array(), \Pdo $pdo = null)
    {
        // default options
        $options = array_replace_recursive(array(
                'with_crh_monitoring' => false,
                'with_annual_review'  => false,
            ),
            $options
        );

        // crh monitoring trigger throught bridge
        if (!empty($options['with_crh_monitoring'])) {
            $this->crhMonitoringBridge->createMonitoring($consultant, null, $pdo); // no date to use default one
        }

        // same for annual review
        if (!empty($options['with_annual_review'])) {
            $this->annualReviewBridge->createReview($consultant, $pdo);
        }

    }
}
