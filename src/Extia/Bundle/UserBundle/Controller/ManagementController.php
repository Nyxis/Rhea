<?php
namespace Extia\Bundle\UserBundle\Controller;

use Extia\Bundle\UserBundle\Model\ConsultantQuery;
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
    public function createAction(Request $request)
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param                                           $id
     * @Template()
     */
    public function deleteCrhAction(Request $request, $id)
    {
        $user = PersonQuery::create()
            ->find();

        if ($user) {
            try {
                $user->delete();
                $this->get('session')->setFlash('notice', 'La création du nouvel utilisateur est valide.');
                $this->redirect($this->generateUrl('extia_user_management_delete_crh'));
            } catch (\Exception $e) {
                $this->get('session')->setFlash('error', 'Impossible de supprimer le compte utilisateur.');

            }
        }
        $this->get('session')->setFlash('error', 'Aucun compte utilisateur à supprimer.');
        $this->redirect($this->generateUrl('extia_user_management_list_crh'));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param                                           $id
     * @Template()
     */
    public function deleteManagerAction(Request $request, $id)
    {
        $user = PersonQuery::create()
            ->findById($id);

        if ($user) {
            try {
                $user->delete();
                $this->get('session')->setFlash('notice', 'La création du nouvel utilisateur est valide.');
                $this->redirect($this->generateUrl('extia_user_management_delete_manager'));
            } catch (\Exception $e) {
                $this->get('session')->setFlash('error', 'Impossible de supprimer le compte utilisateur.');

            }
        }
        $this->get('session')->setFlash('error', 'Aucun compte utilisateur à supprimer.');
        $this->redirect($this->generateUrl('extia_user_management_list_manager'));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param                                           $id
     *
     * @Template()
     */
    public function deleteConsultantAction(Request $request, $id)
    {
        $user = ConsultantQuery::create()
            ->findById($id);

        if ($user) {
            try {
                $user->delete();
                $this->get('session')->setFlash('notice', 'La création du nouvel utilisateur est valide.');
                $this->redirect($this->generateUrl('extia_user_management_delete_consultant'));
            } catch (\Exception $e) {
                $this->get('session')->setFlash('error', 'Impossible de supprimer le compte utilisateur.');
            }
        }
        $this->get('session')->setFlash('error', 'Aucun compte utilisateur à supprimer.');
        $this->redirect($this->generateUrl('extia_user_management_list_consultant'));
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
            $this->redirect($this->generateUrl('Rhea_homepage'));
        }

        return array (
            'form'     => $form->createView(),
            'edit'     => true,
            'hasError' => $request->getMethod() == 'POST' && !$form->isValid()
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @Tamplate()
     */
    public function listConsultantAction(Request $request) {

    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @Tamplate("ExtiaUSerBundle:Management:list.html.twig")
     */
    public function listManagerAction(Request $request) {

    }

    /**
     * @param Request $request
     * @Tamplate("ExtiaUSerBundle:Management:list.html.twig")
     */
    public function listCrhAction(Request $request) {

    }
}
