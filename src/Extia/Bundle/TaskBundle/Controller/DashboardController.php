<?php

namespace Extia\Bundle\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * controller for dashboard screens
 */
class DashboardController extends Controller
{
    /**
     * prints user timeline
     * @param  Request  $request
     * @return Response
     */
    public function userDashboardAction(Request $request)
    {
        return $this->render('ExtiaTaskBundle:Dashboard:user_dashboard.html.twig', array(
            'user' => $this->getUser()
        ));
    }
}
