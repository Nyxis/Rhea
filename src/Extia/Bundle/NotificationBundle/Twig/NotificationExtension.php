<?php

namespace Extia\Bundle\NotificationBundle\Twig;

use Symfony\Component\DependencyInjection\Container;

/**
 * Twig extension for notification rendering
 * @see ExtiaDocumentBundle/Resources/config/services.xml
 */
class NotificationExtension extends \Twig_Extension
{
    protected $container;

    /**
     * construct
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'notification' => new \Twig_Filter_Method($this, 'handleNotification', array('is_safe' => array('html')))
        );
    }

    /**
     * handler of |rendering filter
     * @see NotifierInterface::render()
     * @return string
     */
    public function handleNotification($notification, $type = 'success', array $params = array(), $rendering = 'flat')
    {
        return $this->container->get('notifier')->render($type, $notification, $params, $rendering);
    }

}
