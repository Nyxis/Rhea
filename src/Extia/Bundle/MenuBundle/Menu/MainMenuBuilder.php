<?php

namespace Extia\Bundle\MenuBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Main menu builder
 * @see Extia/Bundle/MenuBundle/Resources/config/services.xml
 */
class MainMenuBuilder
{
    protected $factory;
    protected $translator;

    /**
     * construct
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory, TranslatorInterface $translator)
    {
        $this->factory    = $factory;
        $this->translator = $translator;
    }

    /**
     * creates a new menu item, including twitter bootstrap options
     * @param  MenuItem $menuItem
     * @param  array    $options
     * @return MenuItem created child
     */
    protected function addTbChild(MenuItem $menuItem, $options)
    {
        $child = $menuItem->addChild(
            $this->translator->trans($options['label']),
            array_intersect_key($options, array_flip(array('uri', 'route')))
        );

        if ($options['icon']) {
            $child->setExtra('icon', $options['icon']);
        }
        if ($options['icon-white']) {
            $child->setExtra('icon-white', $options['icon-white']);
        }
        if (isset($options['current']) && !empty($options['current'])) {
            $child->setCurrent(true);
        }

        return $child;
    }

    /**
     * create main menu
     * @param Request $request
     */
    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        // dashboard
        $this->addTbChild($menu, array(
            'label'      => 'menu.dashboard',
            'route'      => 'Rhea_homepage',
            'current'    => $request->get('_menu') == 'dashboard',
            'icon'       => 'list-ul',
            'icon-white' => true
        ));

        // recent activity
        $this->addTbChild($menu, array(
            'label'      => 'menu.recent_activity',
            'uri'        => 'ActivityBundle_recent',
            'current'    => $request->get('_menu') == 'activity',
            'icon'       => 'rss',
            'icon-white' => true
        ));

        // consultants
        $this->addTbChild($menu, array(
            'label'      => 'menu.consultants',
            'uri'        => '#',
            'current'    => $request->get('_menu') == 'consultant',
            'icon'       => 'bug',
            'icon-white' => true
        ));

        // managers
        $this->addTbChild($menu, array(
            'label'      => 'menu.managers',
            'uri'        => '#',
            'icon'       => 'eur',
            'icon-white' => true
        ));

        // crh
        $this->addTbChild($menu, array(
            'label'      => 'menu.crh',
            'uri'        => '#',
            'icon'       => 'group',
            'icon-white' => true
        ));

        // admin
        $this->addTbChild($menu, array(
            'label'      => 'menu.admin',
            'route'      => 'GroupBundle_list',
            'current'    => $request->get('_menu') == 'admin',
            'icon'       => 'cogs',
            'icon-white' => true
        ));

        return $menu;
    }
}
