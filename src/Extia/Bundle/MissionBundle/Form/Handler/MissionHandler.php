<?php
namespace Extia\Bundle\MissionBundle\Form\Handler;

use Extia\Bundle\NotificationBundle\Notification\NotifierInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * handler for mission form
 *
 * @see Extia/Bundles/MissionBundle/Resources/config/admin.xml
 */
class MissionHandler
{
    protected $notifier;
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
    public function __construct(NotifierInterface $notifier, $rootDir, LoggerInterface $logger, $debug)
    {
        $this->notifier        = $notifier;
        $this->rootDir         = $rootDir;
        $this->logger          = $logger;
        $this->debug           = $debug;
    }

    /**
     * @param Form    $form
     * @param Request $request
     * @param Pdo     $pdo     opt pdo connection
     *
     * @return bool
     */
    public function handle(Form $form, Request $request, \Pdo $pdo = null)
    {
        $form->submit($request);
        if (!$form->isValid()) {
            return false;
        }

        if (empty($pdo)) {
            $pdo = \Propel::getConnection('default');
        }

        $pdo->beginTransaction();
        $mission = $form->getData();

        try {
            // image uploading
            if ($form->get('client')->has('image') && $image = $form->get('client')->get('image')->getData()) {
                try {
                    $extension = $image->guessExtension();
                    if (!in_array($extension, array('jpeg', 'png'))) {
                        // $this->notifier->add('warning', 'consultant.admin.notifications.invalid_image');
                    } else {
                        $fileName = $mission->getClient()->getSlug().'.'.$extension;
                        $webPath  = 'images/logos/';
                        $path     = sprintf('%s/../web/%s', $this->rootDir, $webPath);

                        if (!is_dir($path)) {
                            mkdir($path); // will throw an error if access denied caught below
                        }

                        $physicalDoc = $image->move($path, $fileName);
                        $mission->getClient()->setImage($webPath.$fileName);
                    }
                } catch (\Exception $e) {
                    $pdo->rollback();
                    if ($this->debug) {

                        throw $e;
                    }

                    $this->logger->err($e->getMessage());
                    // $this->notifier->add('error', 'consultant.admin.notifications.error_image');
                }
            }

            if ($form->has('client_id') && $clientId = $form->get('client_id')->getData()) {
                $mission->setClientId($clientId);
            } elseif ($form->has('client') && $client = $form->get('client')->getData()) {
                $mission->setClient($client);
            } else {
                // notifier warning
                return false;
            }

            // only client missions can be created
            $mission->setType('client');

            $mission->save($pdo);

            $pdo->commit();

            return true;

        } catch (\Exception $e) {
            $pdo->rollback();

            return false;
        }
    }
}
