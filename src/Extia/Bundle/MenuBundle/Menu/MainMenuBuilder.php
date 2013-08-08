<?php

namespace Extia\Bundle\MenuBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Main menu builder
 * @see Extia/Bundle/MenuBundle/Resources/config/services.xml
 */
class MainMenuBuilder
{
    protected $factory;
    protected $translator;
    protected $securityContext;

    /**
     * construct
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory, TranslatorInterface $translator, SecurityContextInterface $securityContext)
    {
        $this->factory         = $factory;
        $this->translator      = $translator;
        $this->securityContext = $securityContext;
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
        if (isset($options['class']) && !empty($options['class'])) {
            $child->setAttribute('class', $options['class']);
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

        $this->createUserMenu($request, $menu);
        $this->createAdminMenu($request, $menu);

        return $menu;
    }

    /**
     * create user menu
     * @param Request $request
     */
    public function createUserMenu(Request $request, MenuItem $menu)
    {
        $user       = $this->securityContext->getToken()->getUser();
        $teamIds    = $user->getTeamIds();
        $cltIds     = $user->getConsultantsIds();

        $accessTeam = !$teamIds->isEmpty() && $this->securityContext->isGranted('ROLE_INTERNAL_READ', $user);
        $accessClts = (!$cltIds->isEmpty() && $this->securityContext->isGranted('ROLE_CONSULTANT_READ', $user)) // have clt and can read
                        || $this->securityContext->isGranted('ROLE_CONSULTANT_WRITE', $user);      // can create clt

        // have no team, or cannot read user, no menu
        if (!$accessTeam && !$accessClts) {
            return;
        }

        // internals
        if ($accessTeam) {
            $this->addTbChild($menu, array(
                'label'      => 'menu.team',
                'uri'        => '#',
                'current'    => $request->get('_menu') == 'team',
                'icon'       => 'group',
                'icon-white' => true
            ));
        }

        // consultants
        if ($accessClts) {
            $this->addTbChild($menu, array(
                'label'      => 'menu.consultants',
                'route'      => 'UserBundle_consultant_list',
                'current'    => $request->get('_menu') == 'consultant',
                'icon'       => 'bug',
                'icon-white' => true
            ));
        }
    }

    /**
     * create admin menu
     * @param Request $request
     */
    public function createAdminMenu(Request $request, MenuItem $menu)
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN', $this->securityContext->getToken()->getUser())) {
            return;
        }

        // admin
        $adminActive = $request->get('_menu') == 'admin';
        $adminMenu   = $this->addTbChild($menu, array(
            'label'      => 'menu.admin',
            'uri'        => '#',
            'current'    => $adminActive,
            'icon'       => 'cogs',
            'icon-white' => true
        ));
        $adminMenu->setAttribute('class', sprintf('parent%s', $adminActive ? ' open' : ''));

        // managers
        $this->addTbChild($adminMenu, array(
            'label'      => 'menu.managers',
            'uri'        => '#',
            'icon'       => 'eur',
            'icon-white' => true
        ));

        // crh
        $this->addTbChild($adminMenu, array(
            'label'      => 'menu.crh',
            'uri'        => '#',
            'icon'       => 'group',
            'icon-white' => true
        ));

        // groups
        $this->addTbChild($adminMenu, array(
            'label'      => 'menu.groups',
            'route'      => 'GroupBundle_list',
            'current'    => $request->get('_submenu') == 'group',
            'icon'       => 'check',
            'icon-white' => true
        ));

    }

}
