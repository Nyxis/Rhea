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

        // users
        $this->addTbChild($menu, array(
            'label'      => 'menu.users',
            'route'      => 'UserBundle_consultant_list',
            'current'    => $request->get('_menu') == 'users',
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

    /**
     * create user menu
     * @param Request $request
     */
    public function createUserMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        // internals
        $this->addTbChild($menu, array(
            'label'      => 'menu.internals',
            'uri'        => '#',
            'current'    => $request->get('_submenu') == 'internals',
            'icon'       => 'usd',
            'icon-white' => true
        ));

        // consultants
        $this->addTbChild($menu, array(
            'label'      => 'menu.consultants',
            'route'      => 'UserBundle_consultant_list',
            'current'    => $request->get('_submenu') == 'consultant',
            'icon'       => 'bug',
            'icon-white' => true
        ));

        return $menu;
    }

    /**
     * create admin menu
     * @param Request $request
     */
    public function createAdminMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

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

        // groups
        $this->addTbChild($menu, array(
            'label'      => 'menu.groups',
            'route'      => 'GroupBundle_list',
            'current'    => $request->get('_submenu') == 'group',
            'icon'       => 'check',
            'icon-white' => true
        ));

        return $menu;
    }

}
