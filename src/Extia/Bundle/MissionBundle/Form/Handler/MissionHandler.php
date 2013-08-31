<?php
namespace Extia\Bundle\MissionBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 */
class MissionHandler
{
    /**
     * @param Form    $form
     * @param Request $request
     *
     * @return bool
     */
    public function process(Form $form, Request $request)
    {
        $form->submit($request);
        if ($form->isValid()) {
            return false;
        }

        $mission = $form->getData();

        return $mission->save();
    }
}
