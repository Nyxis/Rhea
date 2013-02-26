<?php

namespace EasyTask\Bundle\LogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('EasyTaskLogBundle:Default:index.html.twig', array('name' => $name));
    }
}
