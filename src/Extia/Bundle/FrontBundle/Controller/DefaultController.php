<?php

namespace Extia\Bundle\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('ExtiaFrontBundle:Default:index.html.twig', array('name' => $name));
    }
}
