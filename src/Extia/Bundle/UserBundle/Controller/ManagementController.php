<?php
namespace Extia\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Created by rhea.
 * @author lesmyrmidons <lesmyrmidons@gmail.com>
 * Date: 30/07/13
 * Time: 12:44
 */
class ManagementController extends Controller
{
    /**
     * @param Request $request
     *
     * @return array
     * @Template("ExtiaUserBundle:Management:edit")
     */
    public function addAction(Request $request)
    {
        $form = $this->get('extia_user.person_form');

        $formHandler = $this->get('extia_user.person_form_handler');

        $process = $formHandler->process();

        if ($process) {
            $this->get('session')->setFlash('notice', 'La création du nouvel utilisateur est valide.');
            $this->redirect($this->generateUrl('Rhea_homepage'));
        }

        return array (
            'form'     => $form->createView(),
            'edit'     => null,
            'hasError' => $request->getMethod() == 'POST' && !$form->isValid()
        );
    }

    /**
     * @param Request $request
     * @Template()
     */
    public function deleteAction(Request $request)
    {

    }

    /**
     * @param Request $request
     *
     * @return array
     * @Template("ExtiaUserBundle:Management:edit")
     */
    public function editAction(Request $request)
    {
        $form = $this->get('extia_user.person_form');



        $formHandler = $this->get('extia_user.person_form_handler');

        $process = $formHandler->process();

        if ($process) {
            $this->get('session')->setFlash('notice', 'La création du nouvel utilisateur est valide.');
        }

        return array (
            'form'     => $form->createView(),
            'edit'     => true,
            'hasError' => $request->getMethod() == 'POST' && !$form->isValid()
        );
    }
}
