<?php

namespace Extia\Bundle\TaskBundle\Form\Handler;

use Extia\Bundle\TaskBundle\Model\TaskQuery;

use Extia\Bundle\CommentBundle\Model\Comment;
use Extia\Bundle\NotificationBundle\Notification\NotifierInterface;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * handler for task differing
 * @see ExtiaTaskBundle/Resources/config/services/forms.xml
 */
class DifferTaskHandler
{
    protected $templateEngine;
    protected $notifier;

    /**
     * construct
     * @param EngineInterface $engine
     */
    public function __construct(EngineInterface $engine, NotifierInterface $notifier)
    {
        $this->templateEngine = $engine;
        $this->notifier       = $notifier;
    }

    /**
     * handles given form
     * @param  Form    $form
     * @param  Request $request
     * @return bool
     */
    public function handle(Form $form, Request $request)
    {

    }
}