<?php

namespace Extia\Bundle\MissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Form\Form;

/**
 * controller for missions admin
 */
class AdminMissionController extends Controller
{
    /**
     * list missions action
     * @param  Request  $request
     * @return Response
     */
    public function listAction(Request $request)
    {
        return $this->render('ExtiaMissionBundle:AdminMission:list.html.twig');
    }
}