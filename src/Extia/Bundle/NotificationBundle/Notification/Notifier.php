<?php

namespace Extia\Bundle\NotificationBundle\Notification;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * service class which stores and renders notifications throught session
 * @see NotificationBundle/Resources/config/services.xml
 */
class Notifier implements NotifierInterface
{
    protected $session;
    protected $fragmentHandler;
    protected $templateEngine;
    protected $flatTemplate;
    protected $translator;

    protected $notifType  = array('error', 'warning', 'info', 'success');
    protected $renderType = array('flat', 'template', 'controller');

    /**
     * construct
     * @param SessionInterface    $session
     * @param TranslatorInterface $translator
     * @param FragmentHandler     $fragmentHandler
     * @param EngineInterface     $templateEngine
     * @param string              $flatTemplate
     */
    public function __construct(SessionInterface $session, TranslatorInterface $translator, FragmentHandler $fragmentHandler, EngineInterface $templateEngine, $flatTemplate)
    {
        $this->session         = $session;
        $this->translator      = $translator;
        $this->fragmentHandler = $fragmentHandler;
        $this->templateEngine  = $templateEngine;
        $this->flatTemplate    = $flatTemplate;
    }

    /**
     * @see NotifierInterface::getTypes()
     */
    public function getTypes()
    {
        return $this->notifType;
    }

    /**
     * @see NotifierInterface::add()
     */
    public function add($type, $message, $params = array(), $rendering = 'flat')
    {
        if (!in_array($type, $this->notifType)) {
            throw new \InvalidArgumentException(sprintf(
                'Given notification type is invalid, has to be one of "%s", "%s" given',
                implode('", "', $this->notifType), $type
            ));
        }
        if (!in_array($rendering, $this->renderType)) {
            throw new \InvalidArgumentException(sprintf(
                'Given rendering type is invalid, has to be one of "%s", "%s" given',
                implode('", "', $this->renderType), $rendering
            ));
        }

        $this->session->getFlashbag()->add($type, array(
            'message'   => $message,
            'rendering' => $rendering,
            'params'    => $params
        ));

        return $this;
    }

    /**
     * resolve given notification and return it as string
     * @param  array  $notification
     * @return string
     */
    protected function renderNotification($notification)
    {
        if (empty($notification['message']) || empty($notification['rendering'])) {
            throw new \InvalidArgumentException('Given notification cannot be use, you have to provide "message" and "rendering" keys');
        }

        $message   = $notification['message'];
        $rendering = $notification['rendering'];
        $params    = empty($notification['params']) ? array() : $notification['params'];

        if ($rendering == 'controller') {
            return $this->fragmentHandler->render(new ControllerReference(
                $message, $params
            ));
        }
        if ($rendering == 'template') {
            return $this->templateEngine->render($message, $params);
        }

        return $this->translator->trans($message, $params);
    }

    /**
     * @see NotifierInterface::get()
     */
    public function get($type)
    {
        if (!in_array($type, $this->notifType)) {
            throw new \InvalidArgumentException(sprintf(
                'Given notification type is invalid, has to be one of "%s", "%s" given',
                implode('", "', $this->notifType), $type
            ));
        }

        $resolvedNotifications = array();
        $notificationForType = $this->session->getFlashbag()->get($type);
        foreach ($notificationForType as $notif) {
            $resolvedNotifications[] = $this->templateEngine->render($this->flatTemplate, array(
                'notif' => $this->renderNotification($notif),
                'type'  => $type
            ));
        }

        return $resolvedNotifications;
    }

    /**
     * @see NotifierInterface::all()
     */
    public function all()
    {
        $notifications = array();
        foreach ($this->notifType as $type) {
            $notifications = array_merge($notifications, $this->get($type));
        }

        return $notifications;
    }
}
