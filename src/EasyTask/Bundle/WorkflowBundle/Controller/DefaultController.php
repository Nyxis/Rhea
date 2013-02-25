<?php

namespace EasyTask\Bundle\WorkflowBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('EasyTaskWorkflowBundle:Default:index.html.twig', array('name' => $name));
    }
}
