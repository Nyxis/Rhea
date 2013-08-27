<?php
namespace Extia\Bundle\MissionBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Created by rhea.
 * @author lesmyrmidons <lesmyrmidons@gmail.com>
 * Date: 15/08/13
 * Time: 18:39
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
