<?php

namespace Extia\Bundle\CEOBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CEOController extends Controller
{
    public function CompanyInformationsAction() {

        return $this->render('ExtiaCEOBundle:CEO:company_infos.html.twig', array(
        ));
    }
}
