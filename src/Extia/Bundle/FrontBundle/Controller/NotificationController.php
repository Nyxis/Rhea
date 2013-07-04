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
                'error'   => array('messages' => $flashBags->get('error'),   'icon' => 'bolt'),
                'warning' => array('messages' => $flashBags->get('warning'), 'icon' => 'warning-sign'),
                'info'    => array('messages' => $flashBags->get('info'),    'icon' => 'info'),
                'success' => array('messages' => $flashBags->get('success'), 'icon' => 'ok')
            )
        ));
    }
}
