<?php

namespace Extia\Bundle\MissionBundle\Controller;

use Extia\Bundle\MissionBundle\Model\Company;
use Extia\Bundle\MissionBundle\Model\CompanyQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @return Response
     */
    public function listAjaxAction(Request $request)
    {
        $value = $request->get('q');

        $clients = CompanyQuery::create()->findByTitle('%' . $value . '%');

        $json = array ();
        foreach ($clients as $client) {
            $json[] = array (
                'id'   => $client->getId(),
                'name' => $client->getTitle()
            );
        }

        $response = new JsonResponse();
        $response->setContent(json_encode($json));

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $company = new Company();

        return $this->renderForm($request, $company, 'ExtiaMissionBundle:Company:new.html.twig');

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
        return $this->renderForm($request, $company, 'ExtiaMissionBundle:Company:edit.html.twig');
    }

//    public function deleteAction(Request $request, $name)
//    {
//        return $this->render('ExtiaMissionBundle:Company:index.html.twig', array ('name' => $name));
//    }

    /**
     * @param Request $request
     * @param Company $company
     * @param         $template
     *
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function renderForm(Request $request, Company $company, $template)
    {
        $form = $this->get('form.factory')->create('company', $company, array ());
        $isNew = $company->isNew();

        if ($request->request->has($form->getName())) {
            if ($this->get('extia_mission.form.company_handler')->process($form, $request)) {
                // success message
                $this->get('notifier')->add(
                    'success', 'company.admin.notification.save_success',
                    array ('%company_name%' => $company->getTitle())
                );

                if ($isNew) {
                    return $this->redirect($this->generateUrl('Rhea_homepage'));
                }
            }
        }

        return $this->render($template, array (
            'consultant' => $company,
            'form'       => $form->createView(),
//            'locales'    => $this->container->getParameter('extia_group.managed_locales')
        ));
    }
}
