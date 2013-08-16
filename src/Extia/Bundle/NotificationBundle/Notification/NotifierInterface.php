<?php

namespace Extia\Bundle\NotificationBundle\Notification;

/**
 * Interface for app notifier
 */
interface NotifierInterface
{
    /**
     * @return array all supported notif types
     */
    public function getTypes();

    /**
     * render a notification
     * @param  string $type       notif type
     * @param  string $message    template, controller or string
     * @param  array  $params     template or controller parameters
     * @param  string $rendreding rendering strategy
     * @return string html string of given notification
     */
    public function render($type, $message, $params = array(), $rendering = 'flat');

    /**
     * adds a new notification using session flashbag
     * @param string $type       notif type
     * @param string $message    template, controller or string
     * @param array  $params     template or controller parameters
     * @param string $rendreding rendering strategy
     */
    public function add($type, $message, $params = array(), $rendering = 'flat');

    /**
     * @param  string $type
     * @return array  rendered notifications
     */
    public function get($type);

    /**
     * @return array all types rendered notifications
     */
    public function all();

}
