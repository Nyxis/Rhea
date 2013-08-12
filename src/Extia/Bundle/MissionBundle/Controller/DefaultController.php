<?php

namespace Extia\Bundle\MissionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ExtiaMissionBundle:Default:index.html.twig', array('name' => $name));
    }
}
