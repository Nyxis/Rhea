<?php

namespace Extia\Bundle\MissionBundle\Controller;

use Extia\Bundle\MissionBundle\Model\Mission;
use Extia\Bundle\MissionBundle\Model\MissionQuery;
use Extia\Bundle\UserBundle\Model\MissionOrderQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MissionController extends Controller
{
    public function listAction(Request $request, $consultantId)
    {
        $missions = MissionOrderQuery::create()
                    ->joinWith('Mission m')
                    ->joinWith('m.Client')
                    ->joinWith('m.Manager')
                    ->filterByConsultantId($consultantId)
                    ->find();

        if (empty($missions)) {
            throw new NotFoundHttpException(sprintf('Any mission found for given id, "%s" given.', $consultantId));
        }

        return $this->render('ExtiaMissionBundle:Mission:list.html.twig', array ('missions' => $missions));
    }

    public function newAction(Request $request)
    {
        $mission = new Mission();

        return $this->renderForm($request, $mission, 'ExtiaMissionBundle:Mission:new.html.twig');
    }

    public function editAction(Request $request, $id)
    {
        $mission = MissionQuery::create()
                   ->joinWith('Client')
                   ->joinWith('Internal')
                   ->findByPk($id);

        if (empty($consultant)) {
            throw new NotFoundHttpException(sprintf('Any mission found for given id, "%s" given.', $id));
        }

        return $this->renderForm($request, $mission, 'ExtiaMissionBundle:Mission:edit.html.twig');
    }

    public function deleteAction(Request $request, $id)
    {
    }

    /**
     * @param Request                                   $request
     * @param \Extia\Bundle\MissionBundle\Model\Mission $mission
     * @param                                           $template
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function renderForm(Request $request, Mission $mission, $template)
    {
        $form  = $this->get('form.factory')->create('mission', $mission, array ());
        $isNew = $mission->isNew();

        if ($request->request->has($form->getName())) {
            if ($this->get('extia_mission.form.mission_handler')->process($form, $request)) {

                // success message
                $this->get('notifier')->add(
                    'success', 'mission.admin.notification.save_success',
                    array ('%mission_name%' => $mission->getLabel())
                );

                // redirect on edit if was new
                if ($isNew) {
                    $response = new Response();

                    return $response->setContent(json_encode(array ('success' => true)));
                }
            }
        }

        return $this->render($template, array (
            'consultant' => $mission,
            'form'       => $form->createView(),
//            'locales'    => $this->container->getParameter('extia_group.managed_locales')
        ));
    }
}
