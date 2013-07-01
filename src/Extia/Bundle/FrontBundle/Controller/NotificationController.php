<?php

namespace Extia\Bundle\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * controller for notification system
 */
class NotificationController extends Controller
{
    /**
     * prints notification app template and flashed one
     * @param  Request  $request
     * @return Response
     */
    public function displayAction(Request $request)
    {
        $flashBags = $this->get('session')->getFlashbag();

        return $this->render('ExtiaFrontBundle:Notification:display.html.twig', array(
            'notifications' => array(
                'error'   => $flashBags->get('error'),
                'warning' => $flashBags->get('warning'),
                'info'    => $flashBags->get('info'),
                'success' => $flashBags->get('success')
            )
        ));
    }
}
