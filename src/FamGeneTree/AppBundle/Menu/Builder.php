<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Menu;

use Fgt\Application;
use Fgt\UrlConstants;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Webtrees\LegacyBundle\Legacy\Database;
use Webtrees\LegacyBundle\Legacy\Functions;
use Webtrees\LegacyBundle\Legacy\gedcom_favorites_WT_Module;
use Webtrees\LegacyBundle\Legacy\I18N;
use Webtrees\LegacyBundle\Legacy\Individual;
use Webtrees\LegacyBundle\Legacy\Module;
use Webtrees\LegacyBundle\Legacy\ModuleReportInterface;
use Webtrees\LegacyBundle\Legacy\user_favorites_WT_Module;

class Builder extends ContainerAware
{

    protected $tree_url;

    function __construct()
    {
        $this->tree_url = Application::i()->getTree() ? 'ged=' . Application::i()->getTree()->getNameUrl() : '';
    }

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Home', array('route' => 'webtrees_legacy_homepage'));

//        // access services from the container!
//        $em = $this->container->get('doctrine')->getManager();
//        // findMostRecent and Blog are just imaginary examples
//        $blog = $em->getRepository('AppBundle:Blog')->findMostRecent();
//
//        $menu->addChild('Latest Blog Post', array(
//            'route' => 'blog_show',
//            'routeParameters' => array('id' => $blog->getId())
//        ));

        // you can also add sub level's to your menu's as follows
        $menu->addChild('Edit profile', array('route' => 'fos_user_profile_edit'));

        // ... add more children

        return $menu;
    }

    public function favoriteMenu(FactoryInterface $factory, array $options)
    {

    }

    /**
     * Generate a list of items for the main menu.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return ItemInterface
     */
    public function primaryMenu(FactoryInterface $factory, array $options)
    {
        $controller = Application::i()->getActiveController();
        $tree       = Application::i()->getTree();
        $menu       = $factory->createItem('root');
        $menu->setChildrenAttributes(array('class' => 'primary-menu'));

        if ($tree) {
            $individual = $controller->getSignificantIndividual();
            $menu->addChild($this->menuHomePage($factory, $options));
            $menu->addChild($this->menuChart($factory, $options, $individual));
            $menu->addChild($this->menuLists($factory, $options));
            $menu->addChild($this->menuCalendar($factory, $options));
            $menu->addChild($this->menuReports($factory, $options));
            $menu->addChild($this->menuSearch($factory, $options));

//            $menu->addChild($this->menuModules($factory, $options));

            return $menu;
        } else {
            // No public trees?  No genealogy menu!
            return null;
        }
    }

    /**
     * Generate a list of items for the user menu.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return ItemInterface
     */
    public function secondaryMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttributes(array('class' => 'nav nav-pills secondary-menu'));

        $menu->addChild($this->menuPendingChanges($factory, $options));
        $menu->addChild($this->menuMyPages($factory, $options));
        $menu->addChild($this->menuFavorites($factory, $options));
        $menu->addChild($this->menuLanguages($factory, $options));
        $menu->addChild($this->menuLogin($factory, $options));

        return $menu;
    }

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuCalendar(FactoryInterface $factory, array $options)
    {
//        'uri' => null,
//                'label' => null,
//                'attributes' => array(),
//                'linkAttributes' => array(),
//                'childrenAttributes' => array(),
//                'labelAttributes' => array(),
//                'extras' => array(),
//                'current' => null,
//                'display' => true,
//                'displayChildren' => true,

        if ($this->isSearchEngine()) {
            return $factory->createItem(
                'Calendar',
                [
                    'uri'        => '#',
                    'attributes' => ['id' => 'menu-calendar']
                ]
            );
        }

        // Default action is the day view.
        $menu = $factory->createItem(
            'Calendar',
            [
                'uri'        => UrlConstants::url(UrlConstants::CALENDAR_PHP, $this->tree_url),
                'attributes' => ['id' => 'menu-calendar']
            ]
        );

        // Day view
        $menu->addChild(
            $factory->createItem(
                'Day',
                [
                    'uri'        => UrlConstants::url(UrlConstants::CALENDAR_PHP, $this->tree_url . '&amp;view=day'),
                    'attributes' => ['id' => 'menu-calendar-day']
                ]
            )
        );

        // Month view
        $menu->addChild(
            $factory->createItem(
                'Month',
                [
                    'uri'        => UrlConstants::url(UrlConstants::CALENDAR_PHP, $this->tree_url . '&amp;view=month'),
                    'attributes' => ['id' => 'menu-calendar-month']
                ]
            )
        );
        //Year view
        $menu->addChild(
            $factory->createItem(
                'Year',
                [
                    'uri'        => UrlConstants::url(UrlConstants::CALENDAR_PHP, $this->tree_url . '&amp;view=year'),
                    'attributes' => ['id' => 'menu-calendar-year']
                ]
            )
        );

        return $menu;
    }

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuHomePage(FactoryInterface $factory, array $options)
    {
        $ALLOW_CHANGE_GEDCOM = Application::i()
                                          ->getConfig()
                                          ->get('ALLOW_CHANGE_GEDCOM')
                                          ->asBoolean()
                               && count(Application::i()->getTree()->getAll()) > 1;
        $root                = $factory->createItem('homepage');

        foreach (Application::i()->getTree()->getAll() as $tree) {
            if ($tree->getTreeId() === WT_GED_ID || $ALLOW_CHANGE_GEDCOM) {
                $submenu = $factory->createItem(
                    $tree->getTitle(),
                    array(
                        'uri' => UrlConstants::url(
                            UrlConstants::INDEX_PHP,
                            [
                                'ctype' => 'gedcom',
                                'ged'   => $tree->getNameUrl()
                            ]
                        ),
                        'id'  =>
                            'menu-tree-' . $tree->getTreeId()
                    )
                );
                $root->addChild($submenu);
            }
        }
        $root->setLabel('Family trees');
        $root->setUri(UrlConstants::url(UrlConstants::INDEX_PHP, [
            'ctype' => 'gedcom',
            'ged'   => Application::i()->getTree()->getNameUrl()
        ]));
        $root->setAttribute('id', 'menu-tree');

        return $root;
    }

    /**
     * Generate a menu for each of the different charts.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChart(FactoryInterface $factory, array $options, Individual $individual)
    {
        $tree = Application::i()->getTree();

        if ($tree && !$this->isSearchEngine()) {
            // The top level menu is the pedigree chart
            $menu = $this->menuChartPedigree($factory, $options, $individual);
            $menu->setLabel('Charts');
            $menu->setAttribute('id', 'menu-chart');

            $menu->addChild($this->menuChartAncestors($factory, $options, $individual));
//            $menu->addChild($this->menuChartCompact($factory, $options, $individual));
//            $menu->addChild($this->menuChartDescendants($factory, $options, $individual));
//            $menu->addChild($this->menuChartFamilyBook($factory, $options, $individual));
//            $menu->addChild($this->menuChartFanChart($factory, $options, $individual));
//            $menu->addChild($this->menuChartHourglass($factory, $options, $individual));
//            $menu->addChild($this->menuChartInteractiveTree($factory, $options, $individual));
//            $menu->addChild($this->menuChartLifespan($factory, $options, $individual));
//            $menu->addChild($this->menuChartPedigree($factory, $options, $individual));
//            $menu->addChild($this->menuChartPedigreeMap($factory, $options, $individual));
//            $menu->addChild($this->menuChartRelationship($factory, $options, $individual));
//            $menu->addChild($this->menuChartStatistics($factory, $options, ));
//            $menu->addChild($this->menuChartTimeline($factory, $options, $individual));

            return $menu;
        } else {

            return $this->createMenuUri($factory, $options, 'Charts', '#', 'menu-chart');
        }
    }

    /**
     * Generate a menu item for the ancestors chart (ancestry.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartAncestors(FactoryInterface $factory, array $options, Individual $individual)
    {
        return $factory->createItem('Ancestors',
                                    [
                                        'uri'        => 'ancestry.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url,
                                        'attributes' => ['id' => 'menu-chart-pedigree']
                                    ]);
    }

    /**
     * Generate a menu item for the compact tree (compact.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartCompact(FactoryInterface $factory, array $options, Individual $individual)
    {
        return $factory->createItem('Compact tree', [
            'uri'        => 'compact.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url,
            'attributes' => ['id' => 'menu-chart-compact']
        ]);
    }

    /**
     * Generate a menu item for the descendants chart (descendancy.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartDescendants(FactoryInterface $factory, array $options, Individual $individual)
    {
        return $factory->createItem('Descendants',
                                    [
                                        'uri'        => 'descendancy.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url,
                                        'attributes' => ['id' => 'menu-chart-descendants']
                                    ]);
    }

    /**
     * Generate a menu item for the family-book chart (familybook.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartFamilyBook(FactoryInterface $factory, array $options, Individual $individual)
    {
        return $factory->createItem('Family book',
                                    [
                                        'uri'        => 'familybook.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url,
                                        'attributes' => ['id' => 'menu-chart-familybook']
                                    ]);
    }

    /**
     * Generate a menu item for the fan chart (fanchart.php).
     *
     * We can only do this if the GD2 library is installed with TrueType support.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface|null
     */
    protected function menuChartFanChart(FactoryInterface $factory, array $options, Individual $individual)
    {
        if (function_exists('imagettftext')) {
            return $this->createMenuUri($factory, $options, 'Fan chart', 'fanchart.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url,
                                        'menu-chart-fanchart');
        } else {
            return null;
        }
    }

    /**
     * Generate a menu item for the interactive tree (tree module).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartInteractiveTree(FactoryInterface $factory, array $options, Individual $individual)
    {
        if (array_key_exists('tree', Module::getActiveModules())) {
            return $this->createMenuUri($factory, $options, 'Interactive tree', 'module.php?mod=tree&amp;mod_action=treeview&amp;' . $this->tree_url . '&amp;rootid=' . $individual->getXref(), 'menu-chart-tree');
        } else {
            return null;
        }
    }

    /**
     * Generate a menu item for the hourglass chart (hourglass.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartHourglass(FactoryInterface $factory, array $options, Individual $individual)
    {
        return $this->createMenuUri($factory, $options, 'Hourglass chart', 'hourglass.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-hourglass');
    }

    /**
     * Generate a menu item for the lifepsan chart (lifespan.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartLifespan(FactoryInterface $factory, array $options, Individual $individual)
    {
        return $this->createMenuUri($factory, $options, 'Lifespans', 'lifespan.php?pids%5B%5D=' . $individual->getXref() . '&amp;addFamily=1&amp;' . $this->tree_url, 'menu-chart-lifespan');
    }

    /**
     * Generate a menu item for the pedigree chart (pedigree.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartPedigree(FactoryInterface $factory, array $options, Individual $individual)
    {
        return $this->createMenuUri($factory, $options, 'Pedigree', 'pedigree.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-pedigree');
    }

    /**
     * Generate a menu item for the pedigree map (googlemap module).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartPedigreeMap(FactoryInterface $factory, array $options, Individual $individual)
    {
        if (array_key_exists('googlemap', Module::getActiveModules())) {
            return $this->createMenuUri($factory, $options, 'Pedigree map', 'module.php?' . $this->tree_url . '&amp;mod=googlemap&amp;mod_action=pedigree_map&amp;rootid=' . $individual->getXref(), 'menu-chart-pedigree_map');
        } else {
            return null;
        }
    }

    /**
     * Generate a menu item for the relationship chart (relationship.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartRelationship(FactoryInterface $factory, array $options, Individual $individual)
    {
        if (WT_USER_GEDCOM_ID && $individual->getXref()) {
            return $this->createMenuUri($factory, $options, 'Relationship to me', 'relationship.php?pid1=' . WT_USER_GEDCOM_ID . '&amp;pid2=' . $individual->getXref() . '&amp;ged=' . $this->tree_url, 'menu-chart-relationship');
        } else {
            return $this->createMenuUri($factory, $options, 'Relationships', 'relationship.php?pid1=' . $individual->getXref() . '&amp;ged=' . $this->tree_url, 'menu-chart-relationship');
        }
    }

    /**
     * Generate a menu item for the statistics charts (statistics.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartStatistics(FactoryInterface $factory, array $options)
    {
        return $this->createMenuUri($factory, $options, 'Statistics', 'statistics.php?' . $this->tree_url, 'menu-chart-statistics');
    }

    /**
     * Generate a menu item for the timeline chart (timeline.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuChartTimeline(FactoryInterface $factory, array $options, Individual $individual)
    {
        return $this->createMenuUri($factory, $options, 'Timeline', 'timeline.php?pids%5B%5D=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-timeline');
    }

    /**
     * Generate a menu item for the control panel (admin.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuControlPanel(FactoryInterface $factory, array $options)
    {
        if (WT_USER_GEDCOM_ADMIN) {
            return $this->createMenuUri($factory, $options, 'Control panel', UrlConstants::map(UrlConstants::ADMIN_PHP), 'menu-admin');
        } else {
            return null;
        }
    }

    /**
     * Favorites menu.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuFavorites(FactoryInterface $factory, array $options)
    {
        $controller = Application::i()->getActiveController();

        $show_user_favorites = $this->getCurrentTree() && array_key_exists('user_favorites', Module::getActiveModules())
                               && $this->getAuth()->isLoggedIn();
        $show_tree_favorites = $this->getCurrentTree() && array_key_exists('gedcom_favorites', Module::getActiveModules());

        if ($show_user_favorites && $show_tree_favorites) {
            $favorites = array_merge(
                gedcom_favorites_WT_Module::getFavorites(WT_GED_ID),
                user_favorites_WT_Module::getFavorites($this->getAuth()->getUser()->getId())
            );
        } elseif ($show_user_favorites) {
            $favorites = user_favorites_WT_Module::getFavorites($this->getAuth()->getUser()->getId());
        } elseif ($show_tree_favorites) {
            $favorites = gedcom_favorites_WT_Module::getFavorites(WT_GED_ID);
        } else {
            return null;
        }

        $menu = $this->createMenuUri($factory, $options, 'Favorites', '#', 'menu-favorites');

        foreach ($favorites as $favorite) {
            switch ($favorite['type']) {
                case 'URL':
                    $submenu = $this->createMenuUri($factory, $options, $favorite['title'], $favorite['url']);
                    $menu->addChild($submenu);
                    break;
                case 'INDI':
                case 'FAM':
                case 'SOUR':
                case 'OBJE':
                case 'NOTE':
                    $obj = GedcomRecord::getInstance($favorite['gid']);
                    if ($obj && $obj->canShowName()) {
                        $submenu = $this->createMenuUri($factory, $options, $obj->getFullName(), $obj->getHtmlUrl());
                        $menu->addSubmenu($submenu);
                    }
                    break;
            }
        }

        if ($show_user_favorites) {
            if (isset($controller->record) && $controller->record instanceof GedcomRecord) {
                $submenu = $this->createMenuUri($factory, $options, 'Add to favorites', '#');
                $submenu->setOnclick("jQuery.post('module.php?mod=user_favorites&amp;mod_action=menu-add-favorite',{xref:'" . $controller->record->getXref() . "'},function(){location.reload();})");
                $menu->addChild($submenu);
            }
        }

        return $menu;
    }


    /**
     * A menu to show a list of available languages.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuLanguages(FactoryInterface $factory, array $options)
    {
        $menu = $this->createMenuUri($factory, $options, 'Language', '#', 'menu-language');

        foreach (I18N::installed_languages() as $lang => $name) {
            $submenu = $this->createMenuUri($factory, $options, $name, Functions::i()
                                                                                ->get_query_url(array('lang' => $lang), '&amp;'), 'menu-language-' . $lang);
            if (WT_LOCALE === $lang) {
                $submenu->setAttribute('class', $submenu->getAttribute('class') . ' active');
            }
            $menu->addChild($submenu);
        }

        if (count($menu->getChildren()) > 1 && !$this->isSearchEngine()) {
            return $menu;
        } else {
            return null;
        }
    }

    /**
     * Create a menu to show lists of individuals, families, sources, etc.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     * @throws \Exception
     */
    protected function menuLists(FactoryInterface $factory, array $options)
    {
        $controller = Application::i()->getActiveController();

        // The top level menu shows the individual list
        $menu = $this->createMenuUri($factory, $options, 'Lists', UrlConstants::url(UrlConstants::INDILIST_PHP, $this->tree_url), 'menu-list');

        // Do not show empty lists
        $row = Database::i()->prepare(
            "SELECT SQL_CACHE" .
            " EXISTS(SELECT 1 FROM `##sources` WHERE s_file=?                  ) AS sour," .
            " EXISTS(SELECT 1 FROM `##other`   WHERE o_file=? AND o_type='REPO') AS repo," .
            " EXISTS(SELECT 1 FROM `##other`   WHERE o_file=? AND o_type='NOTE') AS note," .
            " EXISTS(SELECT 1 FROM `##media`   WHERE m_file=?                  ) AS obje"
        )
                       ->execute(array(
                                     WT_GED_ID,
                                     WT_GED_ID,
                                     WT_GED_ID,
                                     WT_GED_ID
                                 ))
                       ->fetchOneRow();

        // Build a list of submenu items and then sort it in localized name order
        $surname_url = '&amp;surname=' . rawurlencode($controller->getSignificantSurname());

        $menu->addChild(
            $this->createMenuUri($factory, $options, 'Individuals', UrlConstants::url(UrlConstants::INDILIST_PHP, $this->tree_url . $surname_url), 'menu-list-indi')
        );

        if (!$this->isSearchEngine()) {
            $menu->addChild($this->createMenuUri($factory, $options, 'Families', UrlConstants::url(UrlConstants::FAMLIST_PHP, $this->tree_url . $surname_url), 'menu-list-fam'));
            $menu->addChild($this->createMenuUri($factory, $options, 'Branches', UrlConstants::url(UrlConstants::BRANCHES_PHP, $this->tree_url . $surname_url), 'menu-branches'));
            $menu->addChild($this->createMenuUri($factory, $options, 'Place hierarchy', UrlConstants::url(UrlConstants::PLACELIST_PHP, $this->tree_url), 'menu-list-plac'));
            if ($row->obje) {
                $menu->addChild($this->createMenuUri($factory, $options, 'Media objects', UrlConstants::url(UrlConstants::MEDIALIST_PHP, $this->tree_url), 'menu-list-obje'));
            }
            if ($row->repo) {
                $menu->addChild($this->createMenuUri($factory, $options, 'Repositories', UrlConstants::url(UrlConstants::REPOLIST_PHP, $this->tree_url), 'menu-list-repo'));
            }
            if ($row->sour) {
                $menu->addChild($this->createMenuUri($factory, $options, 'Sources', UrlConstants::url(UrlConstants::SOURCELIST_PHP, $this->tree_url), 'menu-list-sour'));
            }
            if ($row->note) {
                $menu->addChild($this->createMenuUri($factory, $options, 'Shared notes', UrlConstants::url(UrlConstants::NOTELIST_PHP, $this->tree_url), 'menu-list-note'));
            }
        }

        return $menu;
    }

    /**
     * A login menu option (or null if we are already logged in).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuLogin(FactoryInterface $factory, array $options)
    {
        if ($this->getAuth()->isLoggedIn() || $this->isSearchEngine() || WT_SCRIPT_NAME === UrlConstants::LOGIN_PHP) {
            return $this->createMenuRoute($factory, $options, 'Logout', '_fgt_logout');
        } else {
            return $this->createMenuRoute($factory, $options, 'Login','_fgt_login');

//                                        WT_LOGIN_URL . '?url=' . rawurlencode(Functions::i()
//                                                                                                                    ->get_query_url()));
        }
    }

    /**
     * Get the additional menus created by each of the modules
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuModules(FactoryInterface $factory, array $options)
    {
        $menus = array();
        foreach (Module::getActiveMenus() as $module) {
            $menu = $module->getMenu();
            if ($menu) {
                $menus[] = $menu;
            }
        }

        return $menus;
    }

    /**
     * A link to allow users to edit their account settings (edituser.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuMyAccount(FactoryInterface $factory, array $options)
    {
        if ($this->getAuth()->isLoggedIn()) {
            return $this->createMenuUri($factory, $options, 'My account', 'edituser.php');
        } else {
            return null;
        }
    }

    /**
     * A link to the user's individual record (individual.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuMyIndividualRecord(FactoryInterface $factory, array $options)
    {
        if (WT_USER_GEDCOM_ID) {
            return $this->createMenuUri($factory, $options, 'My individual record', 'individual.php?pid=' . WT_USER_GEDCOM_ID . '&amp;' . $this->tree_url, 'menu-myrecord');
        } else {
            return null;
        }
    }

    /**
     * A link to the user's personal home page.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuMyPage(FactoryInterface $factory, array $options)
    {
        return $this->createMenuUri($factory, $options, 'My page', 'index.php?ctype=user&amp;' . $this->tree_url, 'menu-mypage');
    }

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuMyPages(FactoryInterface $factory, array $options)
    {
        if ($this->getAuth()->isLoggedIn()) {
            $menu = $this->createMenuUri($factory, $options, 'My pages', '#', 'menu-mymenu');
            $menu->addChild($this->menuMyPage($factory, $options));
            $menu->addChild($this->menuMyIndividualRecord($factory, $options));
            $menu->addChild($this->menuMyPedigree($factory, $options));
            $menu->addChild($this->menuMyAccount($factory, $options));
            $menu->addChild($this->menuControlPanel($factory, $options));

            return $menu;
        } else {
            return null;
        }
    }

    /**
     * A link to the user's individual record (pedigree.php).
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuMyPedigree(FactoryInterface $factory, array $options)
    {
        $showFull   = $this->getCurrentTree()->getPreference('PEDIGREE_FULL_DETAILS') ? 1 : 0;
        $showLayout = $this->getCurrentTree()->getPreference('PEDIGREE_LAYOUT') ? 1 : 0;

        if (WT_USER_GEDCOM_ID) {
            return $this->createMenuUri($factory, $options, 'My pedigree',
                                        'pedigree.php?' . $this->tree_url . '&amp;rootid=' . WT_USER_GEDCOM_ID . "&amp;show_full={$showFull}&amp;talloffset={$showLayout}",
                                        'menu-mypedigree'
            );
        } else {
            return null;
        }
    }

    /**
     * Create a pending changes menu.
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuPendingChanges(FactoryInterface $factory, array $options)
    {
        if ($this->pendingChangesExist()) {
            $menu = $this->createMenuUri($factory, $options, 'Pending changes', '#', 'menu-pending');
            $menu->setAttribute('onClick', 'window.open(\'edit_changes.php\', \'_blank\', chan_window_specs); return false;');

            return $menu;
        } else {
            return null;
        }
    }

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuReports(FactoryInterface $factory, array $options)
    {
        $active_reports = Module::getActiveReports();

        if ($this->isSearchEngine() || !$active_reports) {
            return $this->createMenuUri($factory, $options, 'Reports', '#', 'menu-report');
        }

        $menu = $this->createMenuUri($factory, $options, 'Reports', UrlConstants::url(UrlConstants::REPORTENGINE_PHP, $this->tree_url, 'menu-report'));

        $sub_menu = false;
        /** @var ModuleReportInterface $report */
        foreach ($active_reports as $report) {
            foreach ($report->getReportMenus($factory, $options) as $submenu) {
                $menu->addChild($submenu);
                $sub_menu = true;
            }
        }

        if ($sub_menu && !$this->isSearchEngine()) {
            return $menu;
        } else {
            return null;
        }
    }

    /**
     * Create the search menu
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function menuSearch(FactoryInterface $factory, array $options)
    {
        if ($this->isSearchEngine()) {
            return $this->createMenuUri($factory, $options, 'Search', '#', 'menu-search');
        }
        //-- main search menu item
        $menu = $this->createMenuUri($factory, $options, 'Search', 'search.php?' . $this->tree_url, 'menu-search');
        //-- search_general sub menu
        $submenu = $this->createMenuUri($factory, $options, 'General search', 'search.php?' . $this->tree_url, 'menu-search-general');
        $menu->addChild($submenu);
        //-- search_soundex sub menu
        $submenu = $this->createMenuUri($factory, $options,
                                        'Phonetic search', 'search.php?' . $this->tree_url . '&amp;action=soundex', 'menu-search-soundex');
        $menu->addChild($submenu);
        //-- advanced search
        $submenu = $this->createMenuUri($factory, $options, 'Advanced search', 'search_advanced.php?' . $this->tree_url, 'menu-search-advanced');
        $menu->addChild($submenu);
        //-- search_replace sub menu
        if (WT_USER_CAN_EDIT) {
            $submenu = $this->createMenuUri($factory, $options, 'Search and replace', 'search.php?' . $this->tree_url . '&amp;action=replace', 'menu-search-replace');
            $menu->addChild($submenu);
        }

        return $menu;
    }

    /**
     * Links, to show in chart boxes;
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function individualBoxMenu(FactoryInterface $factory, array $options, Individual $individual)
    {
        $menus = array_merge(
            $this->individualBoxMenuCharts($factory, $options, $individual),
            $this->individualBoxMenuFamilyLinks($factory, $options, $individual)
        );

        return $menus;
    }

    /**
     * Chart links, to show in chart boxes;
     *
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param Individual                 $individual
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function individualBoxMenuCharts(FactoryInterface $factory, array $options, Individual $individual)
    {
        $menus = array_filter(array(
                                  $this->menuChartAncestors($factory, $options, $individual),
                                  $this->menuChartCompact($factory, $options, $individual),
                                  $this->menuChartDescendants($factory, $options, $individual),
                                  $this->menuChartFanChart($factory, $options, $individual),
                                  $this->menuChartHourglass($factory, $options, $individual),
                                  $this->menuChartInteractiveTree($factory, $options, $individual),
                                  $this->menuChartPedigree($factory, $options, $individual),
                                  $this->menuChartPedigreeMap($factory, $options, $individual),
                                  $this->menuChartRelationship($factory, $options, $individual),
                                  $this->menuChartTimeline($factory, $options, $individual),
                              ));

        usort($menus, function (Menu $x, Menu $y) {
            return I18N::strcasecmp($x->getLabel(), $y->getLabel());
        });

        return $menus;
    }

    private function isSearchEngine()
    {
        return false;
    }

    /**
     * @param \Knp\Menu\FactoryInterface $factory
     * @param array                      $options
     * @param                            $name
     * @param                            $uri
     * @param                            $id
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function createMenuUri(FactoryInterface $factory, array $options, $name, $uri, $id = null)
    {
        $opts = array_merge(
            [
                'uri' => $uri,
                ['attributes' => ['id' => $id]]
            ],
            $options
        );
        return $factory->createItem($name, $opts);
    }

    protected function createMenuRoute(FactoryInterface $factory, array $options, $name, $route, $id = null)
    {
        $opts = array_merge(
            [
                'route' => $route,
                ['attributes' => ['id' => $id]]
            ],
            $options
        );
        return $factory->createItem($name, $opts);
    }

    private function pendingChangesExist()
    {
        return true;
    }

    /**
     * @return \FamGeneTree\AppBundle\Service\Auth
     */
    protected function getAuth()
    {
        return $this->container->get('fgt.auth');
    }

    /**
     * @return \Webtrees\LegacyBundle\Legacy\Tree
     */
    protected function getCurrentTree()
    {
        return Application::i()->getTree();
    }
}