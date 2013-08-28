<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

use Extia\Bundle\UserBundle\Model\InternalQuery;
use Extia\Bundle\UserBundle\Model\PersonTypeQuery;
use Extia\Bundle\UserBundle\Model\MissionOrder;
// use Extia\Bundle\UserBundle\Model\Resignation;
// use Extia\Bundle\UserBundle\Model\ConsultantQuery;

// use Extia\Bundle\TaskBundle\Model\TaskQuery;
// use Extia\Bundle\MissionBundle\Model\MissionQuery;

use Extia\Bundle\TaskBundle\Workflow\Aggregator;

use Extia\Bundle\NotificationBundle\Notification\NotifierInterface;

use EasyTask\Bundle\WorkflowBundle\Model\Workflow;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Form handler for consultant creation / editing
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class ConsultantHandler
{
    protected $securityContext;
    protected $notifier;
    protected $workflows;
    protected $rootDir;
    protected $logger;
    protected $debug;

    /**
     * construct
     * @param NotifierInterface $notifier
     * @param string            $rootDir
     * @param LoggerInterface   $logger
     * @param bool              $debug
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        NotifierInterface $notifier,
        Aggregator $workflows,
        $rootDir,
        LoggerInterface $logger,
        $debug)
    {
        $this->securityContext = $securityContext;
        $this->notifier        = $notifier;
        $this->workflows       = $workflows;
        $this->rootDir         = $rootDir;
        $this->logger          = $logger;
        $this->debug           = $debug;
    }

    /**
     * handle method
     * @param  Request $request
     * @param  Form    $form
     * @return bool
     */
    public function handle(Form $form, Request $request)
    {
        $form->submit($request);

        if (!$form->isValid()) {
            $this->notifier->add('warning', 'consultant.admin.notifications.invalid_form');

            return false;
        }

        $pdo = \Propel::getConnection('default');
        $pdo->beginTransaction();

        $consultant = $form->getData();
        $image      = $form->get('image')->getData();

        try {
            if (!empty($image)) {   // image uploading

                try {
                    $extension = $image->guessExtension();
                    if (!in_array($extension, array('jpeg', 'png'))) {
                        $this->notifier->add('warning', 'consultant.admin.notifications.invalid_image');
                    } else {
                        $fileName = $consultant->getUrl().'.'.$extension;
                        $webPath  = 'images/avatars/';
                        $path     = sprintf('%s/../web/%s', $this->rootDir, $webPath);

                        if (!is_dir($path)) {
                            mkdir($path); // will throw an error if access denied caught below
                        }

                        $physicalDoc = $image->move($path, $fileName);
                        $consultant->setImage($webPath.$fileName);
                    }
                } catch (\Exception $e) {
                    if ($this->debug) {
                        $pdo->rollback();
                        throw $e;
                    }

                    $this->logger->err($e->getMessage());
                    $this->notifier->add('error', 'consultant.admin.notifications.error_image');
                }
            }

            $crh = InternalQuery::create()
                ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                ->findPk($consultant->getCrhId(), $pdo)
            ;

            $consultant->setPersonTypeId(
                PersonTypeQuery::create()
                    ->setComment(sprintf('%s l:%s', __METHOD__, __LINE__))
                    ->select('Id')
                    ->findOneByCode('clt')
            );

            // mission
            if ($consultant->isNew() && $form->has('on_profile')) {  // replace with form type
                $missionOrder = new MissionOrder();
                $missionOrder->setConsultant($consultant);
                $missionOrder->setMissionId($form->get('mission')->getData());
                $missionOrder->setBeginDate($form->get('begin_at')->getData());
                $missionOrder->setCurrent(true);
            }

            // tmp
            $consultant->setManagerId($form->get('manager_id')->getData());

            // saving with NestedSet
            if ($consultant->isNew()) {
                $consultant->insertAsLastChildOf($crh, $pdo);
            } elseif ($consultant->isColumnModified('CrhId')) {
                $consultant->moveToLastChildOf($crh, $pdo);
            }

            $consultant->save($pdo);

            // success message
            $this->notifier->add(
                'success', 'consultant.admin.notifications.save_success',
                array ('%consultant_name%' => $consultant->getLongName())
            );

            // task creation
            if ($form->has('create_crh_monitoring') && true == $form->get('create_crh_monitoring')->getData()) {
                $wfCrh = $this->workflows->create('crh_monitoring');

                $this->workflows->boot($wfCrh, $request, $pdo);  // first step

                // second step : resolve like an user posted a form
                $task = $this->workflows->getCurrentTask($wfCrh, $pdo);
                $task->getNode()->getType()->getHandler()->resolve(array(
                        'user_target_id' => $consultant->getId(),
                        'next_date'      => $task->findNextWorkingDay(
                            (int) $task->calculateDate($consultant->getContractBeginDate(), '+1 month', 'U')
                        ),
                        'workflow' => array(
                            'name'           => 'Auto gen crh monitoring',
                            'description'    => 'Auto gen crh monitoring'
                        )
                    ), $task, $request, $pdo
                );
            }

            if ($form->has('create_annual_review') && true == $form->get('create_annual_review')->getData()) {
                $wfAnnualReview = $this->workflows->create('annual_review');

                $this->workflows->boot($wfAnnualReview, $request, $pdo);  // first step

                // second step : resolve like an user posted a form
                $task = $this->workflows->getCurrentTask($wfAnnualReview, $pdo);
                $task->getNode()->getType()->getHandler()->resolve(array(
                        'user_target_id' => $consultant->getId(),
                        'next_date'   => $task->findNextWorkingDay(
                            (int) $task->calculateDate($consultant->getContractBeginDate(), '+1 year', 'U')
                        ),
                        'workflow' => array(
                            'name'           => 'Auto gen annual meeting',
                            'description'    => 'Auto gen annual meeting'
                        )
                    ), $task, $request, $pdo
                );
            }

            $pdo->commit();

            return true;

        } catch (\Exception $e) {
            $pdo->rollback();

            if ($this->debug) {
                throw $e;
            }

            $this->logger->err($e->getMessage());
            $this->notifier->add(
                'error', 'internal.admin.notifications.error'
            );

            return false;
        }

    }
}
