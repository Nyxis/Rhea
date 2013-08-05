<?php

namespace Extia\Bundle\NotificationBundle\Controller;

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
        return $this->render('ExtiaNotificationBundle:Notification:display.html.twig', array(
            'notifier' => $this->get('notifier')
        ));
    }
}
