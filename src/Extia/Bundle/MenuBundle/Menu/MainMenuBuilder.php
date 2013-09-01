<?php

namespace Extia\Bundle\MenuBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Main menu builder
 *
 * @see Extia/Bundle/MenuBundle/Resources/config/services.xml
 */
class MainMenuBuilder
{
    protected $factory;
    protected $translator;
    protected $securityContext;

    /**
     * construct
     *
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
     *
     * @param MenuItem $menuItem
     * @param array    $options
     *
     * @return MenuItem created child
     */
    protected function addTbChild(MenuItem $menuItem, $options)
    {
        $child = $menuItem->addChild(
            $this->translator->trans($options['label']),
            array_intersect_key($options, array_flip(array ('uri', 'route')))
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
     *
     * @param Request $request
     */
    public function createMainMenu(Request $request)
    {
        $menu = $this->factory->createItem('root');

        // dashboard
        $this->addTbChild($menu, array (
            'label'      => 'menu.dashboard',
            'route'      => 'Rhea_homepage',
            'current'    => $request->get('_menu') == 'dashboard',
            'icon'       => 'list-ul',
            'icon-white' => true
        ));

        // @2nd milestone
        // // recent activity
        // $this->addTbChild($menu, array (
        //     'label'      => 'menu.recent_activity',
        //     'uri'        => 'ActivityBundle_recent',
        //     'current'    => $request->get('_menu') == 'activity',
        //     'icon'       => 'rss',
        //     'icon-white' => true
        // ));

        $this->createUserMenu($request, $menu);
        $this->createMissionMenu($request, $menu);
        $this->createAdminMenu($request, $menu);

        return $menu;
    }

    /**
     * create user menu
     *
     * @param Request            $request
     * @param \Knp\Menu\MenuItem $menu
     */
    public function createUserMenu(Request $request, MenuItem $menu)
    {
        $user    = $this->securityContext->getToken()->getUser();
        $teamIds = $user->getTeamIds();
        $cltIds  = $user->getConsultantsIds();

        $accessTeam = (!empty($teamIds) && $this->securityContext->isGranted('ROLE_INTERNAL_READ', $user))
            || $this->securityContext->isGranted('ROLE_INTERNAL_WRITE', $user) // can create int
        ;
        $accessClts = (!$cltIds->isEmpty() && $this->securityContext->isGranted('ROLE_CONSULTANT_READ', $user)) // have clt and can read
            || $this->securityContext->isGranted('ROLE_CONSULTANT_WRITE', $user) // can create clt
        ;

        // have no team, or cannot read user, no menu
        if (!$accessTeam && !$accessClts) {
            return;
        }

        // consultants
        if ($accessClts) {
            $this->addTbChild($menu, array (
                'label'      => 'menu.consultants',
                'route'      => 'UserBundle_consultant_list',
                'current'    => $request->get('_menu') == 'consultant',
                'icon'       => 'bug',
                'icon-white' => true
            ));
        }

        // internals
        if ($accessTeam) {
            $this->addTbChild($menu, array (
                'label'      => $this->securityContext->isGranted('ROLE_INTERNAL_WRITE', $user) ? 'menu.internals' : 'menu.team',
                'route'      => 'UserBundle_internal_list',
                'current'    => $request->get('_menu') == 'team',
                'icon'       => 'group',
                'icon-white' => true
            ));
        }
    }

    /**
     * create admin menu
     *
     * @param Request            $request
     * @param \Knp\Menu\MenuItem $menu
     */
    public function createAdminMenu(Request $request, MenuItem $menu)
    {
        if (!$this->securityContext->isGranted('ROLE_ADMIN', $this->securityContext->getToken()->getUser())) {
            return;
        }

        // admin
        $adminActive = $request->get('_menu') == 'admin';
        $adminMenu   = $this->addTbChild($menu, array (
            'label'      => 'menu.admin',
            'uri'        => '#',
            'current'    => $adminActive,
            'icon'       => 'cogs',
            'icon-white' => true
        ));
        $adminMenu->setAttribute('class', sprintf('parent%s', $adminActive ? ' open' : ''));

        // groups
        $this->addTbChild($adminMenu, array (
            'label'      => 'menu.groups',
            'route'      => 'GroupBundle_list',
            'current'    => $request->get('_submenu') == 'group',
            'icon'       => 'check',
            'icon-white' => true
        ));
    }

    /**
     * create mission menu
     *
     * @param  Request  $request
     * @param  MenuItem $menu
     */
    public function createMissionMenu(Request $request, MenuItem $menu)
    {
        $user = $this->securityContext->getToken()->getUser();

        // no access to mission, no menu element
        if (!$this->securityContext->isGranted('ROLE_MISSION_READ', $user)) {
            return;
        }

        // missions
        $this->addTbChild($menu, array (
            'label'      => 'menu.missions',
            'route'      => 'MissionBundle_mission_admin_list',
            'current'    => $request->get('_menu') == 'mission',
            'icon'       => 'eur',
            'icon-white' => true
        ));
    }
}
