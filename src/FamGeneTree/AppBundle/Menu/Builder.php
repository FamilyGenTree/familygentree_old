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
use Webtrees\LegacyBundle\Legacy\I18N;
use Webtrees\LegacyBundle\Legacy\Site;
use Webtrees\LegacyBundle\Legacy\Tree;

class Builder extends ContainerAware
{
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

        if ($tree) {
            $individual = $controller->getSignificantIndividual();
            $menu->addChild($this->menuHomePage());
            $menu->addChild($this->menuChart($individual));
            $menu->addChild($this->menuLists());
            $menu->addChild($this->menuCalendar($factory, $options));
            $menu->addChild($this->menuReports());
            $menu->addChild($this->menuSearch());
            $menu->addChild($this->menuModules());

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
    protected function secondaryMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild($this->menuPendingChanges());
        $menu->addChild($this->menuMyPages());
        $menu->addChild($this->menuFavorites());
        $menu->addChild($this->menuThemes());
        $menu->addChild($this->menuLanguages());
        $menu->addChild($this->menuLogin());
        $menu->addChild($this->menuLogout());

        return $menu;
    }

    /**
     * @return ItemInterface
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
                'uri'        => 'calendar.php?' . $this->tree_url,
                'attributes' => ['id' => 'menu-calendar']
            ]
        );

        // Day view
        $menu->addChild(
            $factory->createItem(
                'Day',
                [
                    'uri'        => 'calendar.php?' . $this->tree_url . '&amp;view=day',
                    'attributes' => ['id' => 'menu-calendar-day']
                ]
            )
        );

        // Month view
        $menu->addChild(
            $factory->createItem(
                'Month',
                [
                    'uri'        => 'calendar.php?' . $this->tree_url . '&amp;view=month',
                    'attributes' => ['id' => 'menu-calendar-month']
                ]
            )
        );
        //Year view
        $menu->addChild(
            $factory->createItem(
                'Year',
                [
                    'uri'        => 'calendar.php?' . $this->tree_url . '&amp;view=year',
                    'attributes' => ['id' => 'menu-calendar-year']
                ]
            )
        );

        return $menu;
    }

    /**
     * Generate a menu for each of the different charts.
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChart(Individual $individual)
    {
        $tree = Application::i()->getTree();

        if ($tree && !$this->isSearchEngine()) {
            // The top level menu is the pedigree chart
            $menu = $this->menuChartPedigree($individual);
            $menu->setLabel('Charts');
            $menu->setAttribute('id', 'menu-chart');

            $menu->addChild($this->menuChartAncestors($individual));
            $menu->addChild($this->menuChartCompact($individual));
            $menu->addChild($this->menuChartDescendants($individual));
            $menu->addChild($this->menuChartFamilyBook($individual));
            $menu->addChild($this->menuChartFanChart($individual));
            $menu->addChild($this->menuChartHourglass($individual));
            $menu->addChild($this->menuChartInteractiveTree($individual));
            $menu->addChild($this->menuChartLifespan($individual));
            $menu->addChild($this->menuChartPedigree($individual));
            $menu->addChild($this->menuChartPedigreeMap($individual));
            $menu->addChild($this->menuChartRelationship($individual));
            $menu->addChild($this->menuChartStatistics());
            $menu->addChild($this->menuChartTimeline($individual));

            return $menu;
        } else {

            return new Menu(I18N::translate('Charts'), '#', 'menu-chart');
        }
    }

    /**
     * Generate a menu item for the ancestors chart (ancestry.php).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartAncestors(Individual $individual)
    {
        return new Menu(I18N::translate('Ancestors'), 'ancestry.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-pedigree');
    }

    /**
     * Generate a menu item for the compact tree (compact.php).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartCompact(Individual $individual)
    {
        return new Menu(I18N::translate('Compact tree'), 'compact.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-compact');
    }

    /**
     * Generate a menu item for the descendants chart (descendancy.php).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartDescendants(Individual $individual)
    {
        return new Menu(I18N::translate('Descendants'), 'descendancy.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-descendants');
    }

    /**
     * Generate a menu item for the family-book chart (familybook.php).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartFamilyBook(Individual $individual)
    {
        return new Menu(I18N::translate('Family book'), 'familybook.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-familybook');
    }

    /**
     * Generate a menu item for the fan chart (fanchart.php).
     *
     * We can only do this if the GD2 library is installed with TrueType support.
     *
     * @param Individual $individual
     *
     * @return ItemInterface|null
     */
    protected function menuChartFanChart(Individual $individual)
    {
        if (function_exists('imagettftext')) {
            return new Menu(I18N::translate('Fan chart'), 'fanchart.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-fanchart');
        } else {
            return null;
        }
    }

    /**
     * Generate a menu item for the interactive tree (tree module).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartInteractiveTree(Individual $individual)
    {
        if (array_key_exists('tree', Module::getActiveModules())) {
            return new Menu(I18N::translate('Interactive tree'), 'module.php?mod=tree&amp;mod_action=treeview&amp;' . $this->tree_url . '&amp;rootid=' . $individual->getXref(), 'menu-chart-tree');
        } else {
            return null;
        }
    }

    /**
     * Generate a menu item for the hourglass chart (hourglass.php).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartHourglass(Individual $individual)
    {
        return new Menu(I18N::translate('Hourglass chart'), 'hourglass.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-hourglass');
    }

    /**
     * Generate a menu item for the lifepsan chart (lifespan.php).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartLifespan(Individual $individual)
    {
        return new Menu(I18N::translate('Lifespans'), 'lifespan.php?pids%5B%5D=' . $individual->getXref() . '&amp;addFamily=1&amp;' . $this->tree_url, 'menu-chart-lifespan');
    }

    /**
     * Generate a menu item for the pedigree chart (pedigree.php).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartPedigree(Individual $individual)
    {
        return new Menu(I18N::translate('Pedigree'), 'pedigree.php?rootid=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-pedigree');
    }

    /**
     * Generate a menu item for the pedigree map (googlemap module).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartPedigreeMap(Individual $individual)
    {
        if (array_key_exists('googlemap', Module::getActiveModules())) {
            return new Menu(I18N::translate('Pedigree map'), 'module.php?' . $this->tree_url . '&amp;mod=googlemap&amp;mod_action=pedigree_map&amp;rootid=' . $individual->getXref(), 'menu-chart-pedigree_map');
        } else {
            return null;
        }
    }

    /**
     * Generate a menu item for the relationship chart (relationship.php).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartRelationship(Individual $individual)
    {
        if (WT_USER_GEDCOM_ID && $individual->getXref()) {
            return new Menu(I18N::translate('Relationship to me'), 'relationship.php?pid1=' . WT_USER_GEDCOM_ID . '&amp;pid2=' . $individual->getXref() . '&amp;ged=' . $this->tree_url, 'menu-chart-relationship');
        } else {
            return new Menu(I18N::translate('Relationships'), 'relationship.php?pid1=' . $individual->getXref() . '&amp;ged=' . $this->tree_url, 'menu-chart-relationship');
        }
    }

    /**
     * Generate a menu item for the statistics charts (statistics.php).
     *
     * @return ItemInterface
     */
    protected function menuChartStatistics()
    {
        return new Menu(I18N::translate('Statistics'), 'statistics.php?' . $this->tree_url, 'menu-chart-statistics');
    }

    /**
     * Generate a menu item for the timeline chart (timeline.php).
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function menuChartTimeline(Individual $individual)
    {
        return new Menu(I18N::translate('Timeline'), 'timeline.php?pids%5B%5D=' . $individual->getXref() . '&amp;' . $this->tree_url, 'menu-chart-timeline');
    }

    /**
     * Generate a menu item for the control panel (admin.php).
     *
     * @return ItemInterface
     */
    protected function menuControlPanel()
    {
        if (WT_USER_GEDCOM_ADMIN) {
            return new Menu(I18N::translate('Control panel'), UrlConstants::map(UrlConstants::ADMIN_PHP), 'menu-admin');
        } else {
            return null;
        }
    }

    /**
     * Favorites menu.
     *
     * @return ItemInterface
     */
    protected function menuFavorites()
    {
        $controller = Application::i()->getActiveController();

        $show_user_favorites = $this->tree && array_key_exists('user_favorites', Module::getActiveModules()) && Auth::check();
        $show_tree_favorites = $this->tree && array_key_exists('gedcom_favorites', Module::getActiveModules());

        if ($show_user_favorites && $show_tree_favorites) {
            $favorites = array_merge(
                gedcom_favorites_WT_Module::getFavorites(WT_GED_ID),
                user_favorites_WT_Module::getFavorites(Auth::id())
            );
        } elseif ($show_user_favorites) {
            $favorites = user_favorites_WT_Module::getFavorites(Auth::id());
        } elseif ($show_tree_favorites) {
            $favorites = gedcom_favorites_WT_Module::getFavorites(WT_GED_ID);
        } else {
            return null;
        }

        $menu = new Menu(I18N::translate('Favorites'), '#', 'menu-favorites');

        foreach ($favorites as $favorite) {
            switch ($favorite['type']) {
                case 'URL':
                    $submenu = new Menu($favorite['title'], $favorite['url']);
                    $menu->addSubmenu($submenu);
                    break;
                case 'INDI':
                case 'FAM':
                case 'SOUR':
                case 'OBJE':
                case 'NOTE':
                    $obj = GedcomRecord::getInstance($favorite['gid']);
                    if ($obj && $obj->canShowName()) {
                        $submenu = new Menu($obj->getFullName(), $obj->getHtmlUrl());
                        $menu->addSubmenu($submenu);
                    }
                    break;
            }
        }

        if ($show_user_favorites) {
            if (isset($controller->record) && $controller->record instanceof GedcomRecord) {
                $submenu = new Menu(I18N::translate('Add to favorites'), '#');
                $submenu->setOnclick("jQuery.post('module.php?mod=user_favorites&amp;mod_action=menu-add-favorite',{xref:'" . $controller->record->getXref() . "'},function(){location.reload();})");
                $menu->addSubmenu($submenu);
            }
        }

        return $menu;
    }

    /**
     * @return ItemInterface
     */
    protected function menuHomePage()
    {
        $submenus            = array();
        $ALLOW_CHANGE_GEDCOM = Site::getPreference('ALLOW_CHANGE_GEDCOM') && count(Tree::getAll()) > 1;

        foreach (Tree::getAll() as $tree) {
            if ($tree->getTreeId() === WT_GED_ID || $ALLOW_CHANGE_GEDCOM) {
                $submenu    = new Menu(
                    $tree->getTitleHtml(),
                    UrlConstants::url(UrlConstants::INDEX_PHP, [
                        'ctype' => 'gedcom',
                        'ged'   => $tree->getNameUrl()
                    ]),
                    'menu-tree-' . $tree->getTreeId()
                );
                $submenus[] = $submenu;
            }
        }

        if (count($submenus) > 1) {
            $label = I18N::translate('Family trees');
        } else {
            $label = I18N::translate('Family trees');
        }

        return new Menu($label, UrlConstants::url(UrlConstants::INDEX_PHP, ['ctype' => 'gedcom']) . $this->tree_url, 'menu-tree', null, $submenus);
    }

    /**
     * A menu to show a list of available languages.
     *
     * @return ItemInterface
     */
    protected function menuLanguages()
    {
        $menu = new Menu(I18N::translate('Language'), '#', 'menu-language');

        foreach (I18N::installed_languages() as $lang => $name) {
            $submenu = new Menu($name, Functions::i()
                                                ->get_query_url(array('lang' => $lang), '&amp;'), 'menu-language-' . $lang);
            if (WT_LOCALE === $lang) {
                $submenu->addClass('', '', 'active');
            }
            $menu->addSubmenu($submenu);
        }

        if (count($menu->getSubmenus()) > 1 && !$this->isSearchEngine()) {
            return $menu;
        } else {
            return null;
        }
    }

    /**
     * Create a menu to show lists of individuals, families, sources, etc.
     *
     * @return ItemInterface
     */
    protected function menuLists()
    {
        $controller = Application::i()->getActiveController();

        // The top level menu shows the individual list
        $menu = new Menu(I18N::translate('Lists'), UrlConstants::url(UrlConstants::INDILIST_PHP, $this->tree_url), 'menu-list');

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

        $menulist = array(
            new Menu(I18N::translate('Individuals'), UrlConstants::url(UrlConstants::INDILIST_PHP, $this->tree_url . $surname_url), 'menu-list-indi'),
        );

        if (!$this->isSearchEngine()) {
            $menulist[] = new Menu(I18N::translate('Families'), UrlConstants::url(UrlConstants::FAMLIST_PHP, $this->tree_url . $surname_url), 'menu-list-fam');
            $menulist[] = new Menu(I18N::translate('Branches'), UrlConstants::url(UrlConstants::BRANCHES_PHP, $this->tree_url . $surname_url), 'menu-branches');
            $menulist[] = new Menu(I18N::translate('Place hierarchy'), UrlConstants::url(UrlConstants::PLACELIST_PHP, $this->tree_url), 'menu-list-plac');
            if ($row->obje) {
                $menulist[] = new Menu(I18N::translate('Media objects'), UrlConstants::url(UrlConstants::MEDIALIST_PHP, $this->tree_url), 'menu-list-obje');
            }
            if ($row->repo) {
                $menulist[] = new Menu(I18N::translate('Repositories'), UrlConstants::url(UrlConstants::REPOLIST_PHP, $this->tree_url), 'menu-list-repo');
            }
            if ($row->sour) {
                $menulist[] = new Menu(I18N::translate('Sources'), UrlConstants::url(UrlConstants::SOURCELIST_PHP, $this->tree_url), 'menu-list-sour');
            }
            if ($row->note) {
                $menulist[] = new Menu(I18N::translate('Shared notes'), UrlConstants::url(UrlConstants::NOTELIST_PHP, $this->tree_url), 'menu-list-note');
            }
        }
        uasort($menulist, function (Menu $x, Menu $y) {
            return I18N::strcasecmp($x->getLabel(), $y->getLabel());
        });

        $menu->setSubmenus($menulist);

        return $menu;
    }

    /**
     * A login menu option (or null if we are already logged in).
     *
     * @return ItemInterface
     */
    protected function menuLogin()
    {
        if (Auth::check() || $this->isSearchEngine() || WT_SCRIPT_NAME === UrlConstants::LOGIN_PHP) {
            return null;
        } else {
            return new Menu(I18N::translate('Login'), WT_LOGIN_URL . '?url=' . rawurlencode(Functions::i()
                                                                                                     ->get_query_url()));
        }
    }

    /**
     * A logout menu option (or null if we are already logged out).
     *
     * @return ItemInterface
     */
    protected function menuLogout()
    {
        if (Auth::check()) {
            return new Menu(I18N::translate('Logout'), 'logout.php');
        } else {
            return null;
        }
    }

    /**
     * Get the additional menus created by each of the modules
     *
     * @return ItemInterface
     */
    protected function menuModules()
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
     * @return ItemInterface
     */
    protected function menuMyAccount()
    {
        if (Auth::check()) {
            return new Menu(I18N::translate('My account'), 'edituser.php');
        } else {
            return null;
        }
    }

    /**
     * A link to the user's individual record (individual.php).
     *
     * @return ItemInterface
     */
    protected function menuMyIndividualRecord()
    {
        if (WT_USER_GEDCOM_ID) {
            return new Menu(I18N::translate('My individual record'), 'individual.php?pid=' . WT_USER_GEDCOM_ID . '&amp;' . $this->tree_url, 'menu-myrecord');
        } else {
            return null;
        }
    }

    /**
     * A link to the user's personal home page.
     *
     * @return ItemInterface
     */
    protected function menuMyPage()
    {
        return new Menu(I18N::translate('My page'), 'index.php?ctype=user&amp;' . $this->tree_url, 'menu-mypage');
    }

    /**
     * @return ItemInterface
     */
    protected function menuMyPages()
    {
        if (Auth::id()) {
            return new Menu(I18N::translate('My pages'), '#', 'menu-mymenu', null, array_filter(array(
                                                                                                    $this->menuMyPage(),
                                                                                                    $this->menuMyIndividualRecord(),
                                                                                                    $this->menuMyPedigree(),
                                                                                                    $this->menuMyAccount(),
                                                                                                    $this->menuControlPanel(),
                                                                                                )));
        } else {
            return null;
        }
    }

    /**
     * A link to the user's individual record (pedigree.php).
     *
     * @return ItemInterface
     */
    protected function menuMyPedigree()
    {
        $showFull   = $this->tree->getPreference('PEDIGREE_FULL_DETAILS') ? 1 : 0;
        $showLayout = $this->tree->getPreference('PEDIGREE_LAYOUT') ? 1 : 0;

        if (WT_USER_GEDCOM_ID) {
            return new Menu(
                I18N::translate('My pedigree'),
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
     * @return ItemInterface
     */
    protected function menuPendingChanges()
    {
        if ($this->pendingChangesExist()) {
            $menu = new Menu(I18N::translate('Pending changes'), '#', 'menu-pending');
            $menu->setOnclick('window.open(\'edit_changes.php\', \'_blank\', chan_window_specs); return false;');

            return $menu;
        } else {
            return null;
        }
    }

    /**
     * @return ItemInterface
     */
    protected function menuReports()
    {
        $active_reports = Module::getActiveReports();

        if ($this->isSearchEngine() || !$active_reports) {
            return new Menu(I18N::translate('Reports'), '#', 'menu-report');
        }

        $menu = new Menu(I18N::translate('Reports'), 'reportengine.php?' . $this->tree_url, 'menu-report');

        $sub_menu = false;
        foreach ($active_reports as $report) {
            foreach ($report->getReportMenus() as $submenu) {
                $menu->addSubmenu($submenu);
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
     * @return ItemInterface
     */
    protected function menuSearch()
    {
        if ($this->isSearchEngine()) {
            return new Menu(I18N::translate('Search'), '#', 'menu-search');
        }
        //-- main search menu item
        $menu = new Menu(I18N::translate('Search'), 'search.php?' . $this->tree_url, 'menu-search');
        //-- search_general sub menu
        $submenu = new Menu(I18N::translate('General search'), 'search.php?' . $this->tree_url, 'menu-search-general');
        $menu->addSubmenu($submenu);
        //-- search_soundex sub menu
        $submenu = new Menu(/* I18N: search using “sounds like”, rather than exact spelling */
            I18N::translate('Phonetic search'), 'search.php?' . $this->tree_url . '&amp;action=soundex', 'menu-search-soundex');
        $menu->addSubmenu($submenu);
        //-- advanced search
        $submenu = new Menu(I18N::translate('Advanced search'), 'search_advanced.php?' . $this->tree_url, 'menu-search-advanced');
        $menu->addSubmenu($submenu);
        //-- search_replace sub menu
        if (WT_USER_CAN_EDIT) {
            $submenu = new Menu(I18N::translate('Search and replace'), 'search.php?' . $this->tree_url . '&amp;action=replace', 'menu-search-replace');
            $menu->addSubmenu($submenu);
        }

        return $menu;
    }

    /**
     * Themes menu.
     *
     * @return ItemInterface
     */
    public function menuThemes()
    {
        if ($this->tree && !$this->isSearchEngine() && Site::getPreference('ALLOW_USER_THEMES') && $this->tree->getPreference('ALLOW_THEME_DROPDOWN')) {
            $submenus = array();
            foreach (Theme::installedThemes() as $theme) {
                $submenu = new Menu($theme->themeName(), Functions::i()
                                                                  ->get_query_url(array('theme' => $theme->themeId()), '&amp;'), 'menu-theme-' . $theme->themeId());
                if ($theme === $this) {
                    $submenu->addClass('', '', 'active');
                }
                $submenus[] = $submenu;
            }

            usort($submenus, function (Menu $x, Menu $y) {
                return I18N::strcasecmp($x->getLabel(), $y->getLabel());
            });

            $menu = new Menu(I18N::translate('Theme'), '#', 'menu-theme', '', $submenus);

            return $menu;
        } else {
            return null;
        }
    }

    /**
     * Links, to show in chart boxes;
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function individualBoxMenu(Individual $individual)
    {
        $menus = array_merge(
            $this->individualBoxMenuCharts($individual),
            $this->individualBoxMenuFamilyLinks($individual)
        );

        return $menus;
    }

    /**
     * Chart links, to show in chart boxes;
     *
     * @param Individual $individual
     *
     * @return ItemInterface
     */
    protected function individualBoxMenuCharts(Individual $individual)
    {
        $menus = array_filter(array(
                                  $this->menuChartAncestors($individual),
                                  $this->menuChartCompact($individual),
                                  $this->menuChartDescendants($individual),
                                  $this->menuChartFanChart($individual),
                                  $this->menuChartHourglass($individual),
                                  $this->menuChartInteractiveTree($individual),
                                  $this->menuChartPedigree($individual),
                                  $this->menuChartPedigreeMap($individual),
                                  $this->menuChartRelationship($individual),
                                  $this->menuChartTimeline($individual),
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

}