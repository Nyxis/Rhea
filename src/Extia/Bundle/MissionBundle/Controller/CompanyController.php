<?php

namespace Extia\Bundle\MissionBundle\Controller;

use Extia\Bundle\MissionBundle\Model\Company;
use Extia\Bundle\MissionBundle\Model\CompanyQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CompanyController extends Controller
{
    /**
     * @param Request $request
     * @param         $name
     */
//    public function listAction(Request $request, $page)
//    {
//        return $this->render('ExtiaMissionBundle:Company:list.html.twig', array ('name' => $name));
//    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $company = new Company();

        $form = $this->renderForm($request, $company);

        return $this->render('ExtiaMissionBundle:Company:new.html.twig', array (
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     * @param Company $company
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     */
    public function editAction(Request $request, Company $company)
    {
        if (empty($company)) {
            throw new NotFoundHttpException(sprintf('Any company found for given id, "%s" given.', $Id));
        }

        $form = $this->renderForm($request, $company);

        return $this->render('ExtiaMissionBundle:Company:edit.html.twig', array (
            'form' => $form->createView(),
        ));
    }

//    public function deleteAction(Request $request, $name)
//    {
//        return $this->render('ExtiaMissionBundle:Company:index.html.twig', array ('name' => $name));
//    }

    private function renderForm(Request $request, Company $company)
    {
        $form = $this->get('form.factory')->create('company', $company, array ());
        $isNew = $company->isNew();

        if ($request->request->has($form->getName())) {
            if ($this->get('extia_mission.form.company_handler')->process($form, $request)) {
                // success message
                $this->get('notifier')->add(
                    'success', 'mission.company.notification.save_success',
                    array ('%company%' => $company->getTitle())
                );

                if ($isNew) {
                    return $this->redirect($this->generateUrl('Rhea_homepage'));
                }
            }
        }

        return $form;
    }
}
