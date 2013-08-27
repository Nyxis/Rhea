<?php

namespace Extia\Bundle\UserBundle\Form\Handler;

// use Extia\Bundle\UserBundle\Model\InternalQuery;
// use Extia\Bundle\UserBundle\Model\Resignation;
// use Extia\Bundle\UserBundle\Model\ConsultantQuery;

// use Extia\Bundle\TaskBundle\Model\TaskQuery;
// use Extia\Bundle\MissionBundle\Model\MissionQuery;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Form handler for consultant creation / editing
 * @see Extia/Bundles/UserBundle/Resources/config/admin.xml
 */
class ConsultantHandler
{

    public function __construct()
    {

    }

    /**
     * handle method
     * @param  Request $request
     * @param  Form    $form
     * @return bool
     */
    public function handle(Form $form, Request $request)
    {
        return false;
    }
}
