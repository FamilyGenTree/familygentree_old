<?php
namespace Webtrees\LegacyBundle\Legacy;

/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use FamGenTree\AppBundle\Service\Session;
use Fgt\Application;
use Fgt\Config;
use Fgt\UrlConstants;

/**
 * Class Base - Common functions and interfaces for all themes.
 */
abstract class BaseTheme
{
    /** @var Session */
    protected $session;

    /** @var Tree The current tree */
    protected $tree;

    /** @var string An escaped version of the "ged=XXX" URL parameter */
    protected $tree_url;

    /** @var boolean Are we showing a page to a search engine? */
    protected $search_engine;

    /**
     * Initialise the theme.  We cannot pass these in a constructor, as the construction
     * happens in a theme file, and we need to be able to change it.
     *
     * @param Session   $session
     * @param bool      $search_engine
     * @param Tree|null $tree The current tree (if there is one).
     *
     */
    public function __construct(Session $session, $search_engine, Tree $tree = null)
    {
        $this->tree          = $tree;
        $this->tree_url      = $tree ? 'ged=' . $tree->getNameUrl() : '';
        $this->session       = $session;
        $this->search_engine = $search_engine;

        $this->hookAfterInit();
    }


    /**
     * Create accessibility links for the header.
     *
     * "Skip to content" allows keyboard only users to navigate over the headers without
     * pressing TAB many times.
     *
     * @return string
     */
    public function accessibilityLinks()
    {
        return
            '<div class="accessibility-links">' .
            '<a class="sr-only sr-only-focusable btn btn-info btn-sm" href="#content">' .
            /* I18N: Skip over the headers and menus, to the main content of the page */
            I18N::translate('Skip to content') .
            '</a>' .
            '</div>';
    }

    /**
     * Create scripts for analytics and tracking.
     *
     * @return string
     */
    public function analytics()
    {
        if ($this->themeId() === '_administration') {
            return '';
        } else {
            return
                $this->analyticsGoogleWebmaster(
                    Site::getPreference('GOOGLE_WEBMASTER_ID')
                ) .
                $this->analyticsGoogleTracker(
                    Site::getPreference('GOOGLE_ANALYTICS_ID')
                ) .
                $this->analyticsPiwikTracker(
                    Site::getPreference('PIWIK_URL'),
                    Site::getPreference('PIWIK_SITE_ID')
                ) .
                $this->analyticsStatcounterTracker(
                    Site::getPreference('STATCOUNTER_PROJECT_ID'),
                    Site::getPreference('STATCOUNTER_SECURITY_ID')
                );
        }
    }

    /**
     * Create the verification code for Google Webmaster Tools.
     *
     * @param string $verification_id
     *
     * @return string
     */
    protected function analyticsBingWebmaster($verification_id)
    {
        // Only need to add this to the home page.
        if (WT_SCRIPT_NAME === UrlConstants::INDEX_PHP && $verification_id) {
            return '<meta name="msvalidate.01" content="' . $verification_id . '">';
        } else {
            return '';
        }
    }

    /**
     * Create the verification code for Google Webmaster Tools.
     *
     * @param string $verification_id
     *
     * @return string
     */
    protected function analyticsGoogleWebmaster($verification_id)
    {
        // Only need to add this to the home page.
        if (WT_SCRIPT_NAME === UrlConstants::INDEX_PHP && $verification_id) {
            return '<meta name="google-site-verification" content="' . $verification_id . '">';
        } else {
            return '';
        }
    }

    /**
     * Create the tracking code for Google Analytics.
     *
     * @param string $analytics_id
     *
     * @return string
     */
    protected function analyticsGoogleTracker($analytics_id)
    {
        if ($analytics_id) {
            return
                '<script>' .
                '(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){' .
                '(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),' .
                'm=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)' .
                '})(window,document,"script","//www.google-analytics.com/analytics.js","ga");' .
                'ga("create", "' . $analytics_id . '", "auto");' .
                'ga("send", "pageview");' .
                '</script>';
        } else {
            return '';
        }
    }

    /**
     * Create the tracking code for Piwik Analytics.
     *
     * @param string $url     - The domain/path to Piwik
     * @param string $site_id - The Piwik site identifier
     *
     * @return string
     */
    protected function analyticsPiwikTracker($url, $site_id)
    {
        if ($url && $site_id) {
            return
                '<script>' .
                'var _paq=_paq||[];' .
                '(function(){var u=(("https:"==document.location.protocol)?"https://' . $url . '/":"http://' . $url . '/");' .
                '_paq.push(["setSiteId",' . $site_id . ']);' .
                '_paq.push(["setTrackerUrl",u+"piwik.php"]);' .
                '_paq.push(["trackPageView"]);' .
                '_paq.push(["enableLinkTracking"]);' .
                'var d=document,g=d.createElement("script"),s=d.getElementsByTagName("script")[0];g.defer=true;g.async=true;g.src=u+"piwik.js";' .
                's.parentNode.insertBefore(g,s);})();' .
                '</script>';
        } else {
            return '';
        }
    }

    /**
     * Create the tracking code for Statcounter.
     *
     * @param string $project_id  - The statcounter project ID
     * @param string $security_id - The statcounter security ID
     *
     * @return string
     */
    protected function analyticsStatcounterTracker($project_id, $security_id)
    {
        if ($project_id && $security_id) {
            return
                '<script>' .
                'var sc_project=' . (int)$project_id . ',sc_invisible=1,sc_security="' . $security_id .
                '",scJsHost = (("https:"===document.location.protocol)?"https://secure.":"http://www.");' .
                'document.write("<sc"+"ript src=\'"+scJsHost+"statcounter.com/counter/counter.js\'></"+"script>");' .
                '</script>';
        } else {
            return '';
        }
    }

    /**
     * Where are our CSS, JS and other assets?
     *
     * @return string A relative path, such as "themes/foo/"
     */
    public function assetUrl()
    {
        return '';
    }

    /**
     * Create a contact link for a user.
     *
     * @param User $user
     *
     * @return string
     */
    public function contactLink(User $user)
    {
        $method = $user->getPreference('contactmethod');

        switch ($method) {
            case 'none':
                return '';
            case 'mailto':
                return '<a href="mailto:' . Filter::escapeHtml($user->getEmail()) . '">' . Filter::escapeHtml($user->getRealName()) . '</a>';
            default:
                return "<a href='#' onclick='message(\"" . Filter::escapeHtml($user->getUserName()) . "\", \"" . $method . "\", \"" . Config::get(Config::BASE_URL) . Filter::escapeHtml(Functions::i()
                                                                                                                                                                                                  ->get_query_url()) . "\", \"\");return false;'>" . Filter::escapeHtml($user->getRealName()) . '</a>';
        }
    }

    /**
     * Create contact link for both technical and genealogy support.
     *
     * @param User $user
     *
     * @return string
     */
    protected function contactLinkEverything(User $user)
    {
        return I18N::translate('For technical support or genealogy questions, please contact') . ' ' . $this->contactLink($user);
    }

    /**
     * Create contact link for genealogy support.
     *
     * @param User $user
     *
     * @return string
     */
    protected function contactLinkGenealogy(User $user)
    {
        return I18N::translate('For help with genealogy questions contact') . ' ' . $this->contactLink($user);
    }

    /**
     * Create contact link for technical support.
     *
     * @param User $user
     *
     * @return string
     */
    protected function contactLinkTechnical(User $user)
    {
        return I18N::translate('For technical support and information contact') . ' ' . $this->contactLink($user);
    }

    /**
     * Create contact links for the page footer.
     *
     * @return string
     */
    protected function contactLinks()
    {
        $contact_user   = User::find($this->tree->getPreference('CONTACT_USER_ID'));
        $webmaster_user = User::find($this->tree->getPreference('WEBMASTER_USER_ID'));

        if ($contact_user && $contact_user === $webmaster_user) {
            return $this->contactLinkEverything($contact_user);
        } elseif ($contact_user && $webmaster_user) {
            return $this->contactLinkGenealogy($contact_user) . '<br>' . $this->contactLinkTechnical($webmaster_user);
        } elseif ($contact_user) {
            return $this->contactLinkGenealogy($contact_user);
        } elseif ($webmaster_user) {
            return $this->contactLinkTechnical($webmaster_user);
        } else {
            return '';
        }
    }

    /**
     * Create the <footer> tag.
     *
     * @return string
     */
    public function footerContainer()
    {
        return
            '</main>' .
            '<footer>' . $this->footerContent() . '</footer>';
    }

    /**
     * Create the contents of the <footer> tag.
     *
     * @return string
     */
    protected function footerContent()
    {
        return
            $this->formatContactLinks() .
            $this->logoPoweredBy();
    }

    /**
     * Format the contents of a variable-height home-page block.
     *
     * @param string $id
     * @param string $title
     * @param string $class
     * @param string $content
     *
     * @return string
     */
    public function formatBlock($id, $title, $class, $content)
    {
        return
            '<div id="' . $id . '" class="block" >' .
            '<div class="blockheader">' . $title . '</div>' .
            '<div class="blockcontent ' . $class . '">' . $content . '</div>' .
            '</div>';
    }

    /**
     * Add markup to the contact links.
     *
     * @return string
     */
    protected function formatContactLinks()
    {
        if ($this->tree) {
            return '<div class="contact-links">' . $this->contactLinks() . '</div>';
        } else {
            return '';
        }
    }

    /**
     * Create a pending changes link for the page footer.
     *
     * @return string
     */
    protected function formatPendingChangesLink()
    {
        if ($this->pendingChangesExist()) {
            return '<div class="pending-changes-link">' . $this->pendingChangesLink() . '</div>';
        } else {
            return '';
        }
    }

    /**
     * Create a quick search form for the header.
     *
     * @return string
     */
    protected function formQuickSearch()
    {
        if ($this->tree) {
            return
                '<form action="search.php" class="header-search" method="post" role="search">' .
                '<input type="hidden" name="action" value="general">' .
                '<input type="hidden" name="ged" value="' . $this->tree->getNameHtml() . '">' .
                '<input type="hidden" name="topsearch" value="yes">' .
                $this->formQuickSearchFields() .
                '</form>';
        } else {
            return '';
        }
    }

    /**
     * Create a search field and submit button for the quick search form in the header.
     *
     * @return string
     */
    protected function formQuickSearchFields()
    {
        return
            '<input type="search" name="query" size="15" placeholder="' . I18N::translate('Search') . '">' .
            '<input type="image" src="' . Application::i()->getTheme()
                                               ->parameter('image-search') . '" alt="' . I18N::translate('Search') . '">';
    }

    /**
     * Add markup to the secondary menu.
     *
     * @return string
     */
    protected function formatSecondaryMenu()
    {
        return
            '<ul class="secondary-menu">' .
            implode('', $this->secondaryMenu()) .
            '</ul>';
    }

    /**
     * Add markup to an item in the secondary menu.
     *
     * @param Menu $menu
     *
     * @return string
     */
    protected function formatSecondaryMenuItem(Menu $menu)
    {
        return $menu->getMenuAsList();
    }

    /**
     * Create the <head> tag.
     *
     * @param PageController $controller The current controller
     *
     * @return string
     */
    public function head(PageController $controller)
    {
        return
            '<head>' .
            $this->headContents($controller) .
            $this->hookHeaderExtraContent() .
            $this->analytics() .
            '</head>';
    }

    /**
     * Create the contents of the <head> tag.
     *
     * @param PageControllerInterface $controller The current controller
     *
     * @return string
     */
    public function headContents(PageControllerInterface $controller)
    {
        // The title often includes the names of records, which may include HTML markup.
        $title = Filter::unescapeHtml($controller->getPageTitle());

        // If an extra (site) title is specified, append it.
        if ($this->tree && $this->tree->getPreference('META_TITLE')) {
            $title .= ' - ' . Filter::escapeHtml($this->tree->getPreference('META_TITLE'));
        }

        $html =
            // modernizr.js and respond.js need to be loaded before the <body> to avoid FOUC
            '<!--[if IE 8]><script src="' . WT_MODERNIZR_JS_URL . '"></script><![endif]-->' .
            '<!--[if IE 8]><script src="' . WT_RESPOND_JS_URL . '"></script><![endif]-->' .
            $this->title($title) .
            $this->favicon()
            ;

        // CSS files
        foreach ($this->stylesheets() as $css) {
            $html .= '<link rel="stylesheet" type="text/css" href="' . $css . '">';
        }

        return $html;
    }

    /**
     * Allow themes to do things after initialization (since they cannot use
     * the constructor).
     *
     * @return void
     */
    public function hookAfterInit()
    {
    }

    /**
     * Allow themes to add extra scripts to the page footer.
     *
     * @return string
     */
    public function hookFooterExtraJavascript()
    {
        return '';
    }

    /**
     * Allow themes to add extra content to the page header.
     * Typically this will be additional CSS.
     *
     * @return string
     */
    public function hookHeaderExtraContent()
    {
        return '';
    }

    /**
     * Create the <html> tag.
     *
     * @return string
     */
    public function html()
    {
        return '<html ' . I18N::html_markup() . '>';
    }

    /**
     * Add HTML markup to create an alert
     *
     * @param string  $html        The content of the alert
     * @param string  $level       One of 'success', 'info', 'warning', 'danger'
     * @param boolean $dismissible If true, add a close button.
     *
     * @return string
     */
    public function htmlAlert($html, $level, $dismissible)
    {
        if ($dismissible) {
            return
                '<div class="alert alert-' . $level . ' alert-dismissible" role="alert">' .
                '<button type="button" class="close" data-dismiss="alert" aria-label="' . I18N::translate('close') . '">' .
                '<span aria-hidden="true">&times;</span>' .
                '</button>' .
                $html .
                '</div>';
        } else {
            return
                '<div class="alert alert-' . $level . '" role="alert">' .
                $html .
                '</div>';
        }
    }

    /**
     * Add HTML markup to create a group of radio buttons
     *
     * @param string $name   The form name of the controls
     * @param string $legend A description of the group of controls
     *
     * @return string
     */
    public function htmlRadioButtons($name, $legend)
    {
        return '<fieldset><legend>' . $legend . '</legend></fieldset>';
    }

    /**
     * Display an icon for this fact.
     *
     * @param Fact $fact
     *
     * @return string
     */
    public function icon(Fact $fact)
    {
        $icon = 'images/facts/' . $fact->getTag() . '.png';
        $dir  = substr($this->assetUrl(), strlen(WT_STATIC_URL));
        if (file_exists($dir . $icon)) {
            return '<img src="' . $this->assetUrl() . $icon . '" title="' . WT_Gedcom_Tag::getLabel($fact->getTag()) . '">';
        } elseif (file_exists($dir . 'images/facts/NULL.png')) {
            // Spacer image - for alignment - until we move to a sprite.
            return '<img src="' . Application::i()->getTheme()
                                       ->assetUrl() . 'images/facts/NULL.png">';
        } else {
            return '';
        }
    }

    /**
     * Display an individual in a box - for charts, etc.
     *
     * @param Individual $individual
     *
     * @return string
     */
    public function individualBox(Individual $individual)
    {
        $personBoxClass = array_search($individual->getSex(), array(
            'person_box'   => 'M',
            'person_boxF'  => 'F',
            'person_boxNN' => 'U'
        ));
        if ($this->tree->getPreference('SHOW_HIGHLIGHT_IMAGES')) {
            $thumbnail = $individual->displayImage();
        } else {
            $thumbnail = '';
        }

        return
            '<div data-pid="' . $individual->getXref() . '" class="person_box_template ' . $personBoxClass . ' box-style1" style="width: ' . $this->parameter('chart-box-x') . 'px; min-height: ' . $this->parameter('chart-box-y') . 'px">' .
            '<div class="noprint icons">' .
            '<span class="iconz icon-zoomin" title="' . I18N::translate('Zoom in/out on this box.') . '"></span>' .
            '<div class="itr"><i class="icon-pedigree"></i><div class="popup">' .
            '<ul class="' . $personBoxClass . '">' . implode('', $this->individualBoxMenu($individual)) . '</ul>' .
            '</div>' .
            '</div>' .
            '</div>' .
            '<div class="chart_textbox" style="max-height:' . $this->parameter('chart-box-y') . 'px;">' .
            $thumbnail .
            '<a href="' . $individual->getHtmlUrl() . '">' .
            '<span class="namedef name1">' . $individual->getFullName() . '</span>' .
            '</a>' .
            '<div class="namedef name1">' . $individual->getAddName() . '</div>' .
            '<div class="inout2 details1">' . $this->individualBoxFacts($individual) . '</div>' .
            '</div>' .
            '<div class="inout"></div>' .
            '</div>';
    }

    /**
     * Display an empty box - for a missing individual in a chart.
     *
     * @return string
     */
    public function individualBoxEmpty()
    {
        return '<div class="person_box_template person_boxNN box-style1" style="width: ' . $this->parameter('chart-box-x') . 'px; min-height: ' . $this->parameter('chart-box-y') . 'px"></div>';
    }

    /**
     * Display an individual in a box - for charts, etc.
     *
     * @param Individual $individual
     *
     * @return string
     */
    public function individualBoxLarge(Individual $individual)
    {
        $personBoxClass = array_search($individual->getSex(), array(
            'person_box'   => 'M',
            'person_boxF'  => 'F',
            'person_boxNN' => 'U'
        ));
        if ($this->tree->getPreference('SHOW_HIGHLIGHT_IMAGES')) {
            $thumbnail = $individual->displayImage();
        } else {
            $thumbnail = '';
        }

        return
            '<div data-pid="' . $individual->getXref() . '" class="person_box_template ' . $personBoxClass . ' box-style2">' .
            '<div class="noprint icons">' .
            '<span class="iconz icon-zoomin" title="' . I18N::translate('Zoom in/out on this box.') . '"></span>' .
            '<div class="itr"><i class="icon-pedigree"></i><div class="popup">' .
            '<ul class="' . $personBoxClass . '">' . implode('', $this->individualBoxMenu($individual)) . '</ul>' .
            '</div>' .
            '</div>' .
            '</div>' .
            '<div class="chart_textbox" style="max-height:' . $this->parameter('chart-box-y') . 'px;">' .
            $thumbnail .
            '<a href="' . $individual->getHtmlUrl() . '">' .
            '<span class="namedef name2">' . $individual->getFullName() . '</span>' .
            '</a>' .
            '<div class="namedef name2">' . $individual->getAddName() . '</div>' .
            '<div class="inout2 details2">' . $this->individualBoxFacts($individual) . '</div>' .
            '</div>' .
            '<div class="inout"></div>' .
            '</div>';
    }

    /**
     * Display an individual in a box - for charts, etc.
     *
     * @param Individual $individual
     *
     * @return string
     */
    public function individualBoxSmall(Individual $individual)
    {
        $personBoxClass = array_search($individual->getSex(), array(
            'person_box'   => 'M',
            'person_boxF'  => 'F',
            'person_boxNN' => 'U'
        ));
        if ($this->tree->getPreference('SHOW_HIGHLIGHT_IMAGES')) {
            $thumbnail = $individual->displayImage();
        } else {
            $thumbnail = '';
        }


        return
            '<div data-pid="' . $individual->getXref() . '" class="person_box_template ' . $personBoxClass . ' box-style0" style="width: ' . $this->parameter('compact-chart-box-x') . 'px; min-height: ' . $this->parameter('compact-chart-box-y') . 'px">' .
            '<div class="compact_view">' .
            $thumbnail .
            '<a href="' . $individual->getHtmlUrl() . '">' .
            '<span class="namedef name0">' . $individual->getFullName() . '</span>' .
            '</a>' .
            '<div class="inout2 details0">' . $individual->getLifeSpan() . '</div>' .
            '</div>' .
            '<div class="inout"></div>' .
            '</div>';
    }

    /**
     * Display an individual in a box - for charts, etc.
     *
     * @return string
     */
    public function individualBoxSmallEmpty()
    {
        return '<div class="person_box_template person_boxNN box-style1" style="width: ' . $this->parameter('compact-chart-box-x') . 'px; min-height: ' . $this->parameter('compact-chart-box-y') . 'px"></div>';
    }

    /**
     * Generate the facts, for display in charts.
     *
     * @param Individual $individual
     *
     * @return string
     */
    protected function individualBoxFacts(Individual $individual)
    {
        $html = '';

        $opt_tags = preg_split('/\W/', $this->tree->getPreference('CHART_BOX_TAGS'), 0, PREG_SPLIT_NO_EMPTY);
        // Show BIRT or equivalent event
        foreach (explode('|', WT_EVENTS_BIRT) as $birttag) {
            if (!in_array($birttag, $opt_tags)) {
                $event = $individual->getFirstFact($birttag);
                if ($event) {
                    $html .= $event->summary();
                    break;
                }
            }
        }
        // Show optional events (before death)
        foreach ($opt_tags as $key => $tag) {
            if (!preg_match('/^(' . WT_EVENTS_DEAT . ')$/', $tag)) {
                $event = $individual->getFirstFact($tag);
                if (!is_null($event)) {
                    $html .= $event->summary();
                    unset ($opt_tags[$key]);
                }
            }
        }
        // Show DEAT or equivalent event
        foreach (explode('|', WT_EVENTS_DEAT) as $deattag) {
            $event = $individual->getFirstFact($deattag);
            if ($event) {
                $html .= $event->summary();
                if (in_array($deattag, $opt_tags)) {
                    unset ($opt_tags[array_search($deattag, $opt_tags)]);
                }
                break;
            }
        }
        // Show remaining optional events (after death)
        foreach ($opt_tags as $tag) {
            $event = $individual->getFirstFact($tag);
            if ($event) {
                $html .= $event->summary();
            }
        }

        return $html;
    }

    /**
     * Generate the LDS summary, for display in charts.
     *
     * @param Individual $individual
     *
     * @return string
     */
    protected function individualBoxLdsSummary(Individual $individual)
    {
        if ($this->tree->getPreference('SHOW_LDS_AT_GLANCE')) {
            $BAPL = $individual->getFacts('BAPL') ? 'B' : '_';
            $ENDL = $individual->getFacts('ENDL') ? 'E' : '_';
            $SLGC = $individual->getFacts('SLGC') ? 'C' : '_';
            $SLGS = '_';

            foreach ($individual->getSpouseFamilies() as $family) {
                if ($family->getFacts('SLGS')) {
                    $SLGS = '';
                }
            }

            return $BAPL . $ENDL . $SLGS . $SLGC;
        } else {
            return '';
        }
    }


    /**
     * Family links, to show in chart boxes.
     *
     * @param Individual $individual
     *
     * @return Menu[]
     */
    protected function individualBoxMenuFamilyLinks(Individual $individual)
    {
        $menus = array();

        foreach ($individual->getSpouseFamilies() as $family) {
            $menus[] = new Menu('<strong>' . I18N::translate('Family with spouse') . '</strong>', $family->getHtmlUrl());
            $spouse  = $family->getSpouse($individual);
            if ($spouse && $spouse->canShowName()) {
                $menus[] = new Menu($spouse->getFullName(), $spouse->getHtmlUrl());
            }
            foreach ($family->getChildren() as $child) {
                if ($child->canShowName()) {
                    $menus[] = new Menu($child->getFullName(), $child->getHtmlUrl());
                }
            }
        }

        return $menus;
    }

    /**
     * Create part of an individual box
     *
     * @param Individual $individual
     *
     * @return string
     */
    protected function individualBoxSexSymbol(Individual $individual)
    {
        if ($this->tree->getPreference('PEDIGREE_SHOW_GENDER')) {
            return $individual->sexImage('large');
        } else {
            return '';
        }
    }

    /**
     * Are we generating a page for a robot (instead of a human being).
     *
     * @return boolean
     */
    protected function isSearchEngine()
    {
        return $this->search_engine;
    }

    /**
     * Misecellaneous dimensions, fonts, styles, etc.
     *
     * @param string $parameter_name
     *
     * @return string|integer|float
     */
    public function parameter($parameter_name)
    {
        $parameters = array(
            'chart-background-f'             => 'dddddd',
            'chart-background-m'             => 'cccccc',
            'chart-background-u'             => 'eeeeee',
            'chart-box-x'                    => 250,
            'chart-box-y'                    => 80,
            'chart-descendancy-box-x'        => 260,
            'chart-descendancy-box-y'        => 80,
            'chart-descendancy-indent'       => 15,
            'chart-font-color'               => '000000',
            'chart-font-name'                => WT_ROOT . 'includes/fonts/DejaVuSans.ttf',
            'chart-font-size'                => 7,
            'chart-offset-x'                 => 10,
            'chart-offset-y'                 => 10,
            'chart-spacing-x'                => 1,
            'chart-spacing-y'                => 5,
            'compact-chart-box-x'            => 240,
            'compact-chart-box-y'            => 50,
            'distribution-chart-high-values' => '555555',
            'distribution-chart-low-values'  => 'cccccc',
            'distribution-chart-no-values'   => 'ffffff',
            'distribution-chart-x'           => 440,
            'distribution-chart-y'           => 220,
            'line-width'                     => 1.5,
            'shadow-blur'                    => 0,
            'shadow-color'                   => '',
            'shadow-offset-x'                => 0,
            'shadow-offset-y'                => 0,
            'stats-small-chart-x'            => 440,
            'stats-small-chart-y'            => 125,
            'stats-large-chart-x'            => 900,
            'image-dline'                    => $this->assetUrl() . 'images/dline.png',
            'image-dline2'                   => $this->assetUrl() . 'images/dline2.png',
            'image-hline'                    => $this->assetUrl() . 'images/hline.png',
            'image-spacer'                   => $this->assetUrl() . 'images/spacer.png',
            'image-vline'                    => $this->assetUrl() . 'images/vline.png',
            'image-add'                      => $this->assetUrl() . 'images/add.png',
            'image-button_family'            => $this->assetUrl() . 'images/buttons/family.png',
            'image-minus'                    => $this->assetUrl() . 'images/minus.png',
            'image-plus'                     => $this->assetUrl() . 'images/plus.png',
            'image-remove'                   => $this->assetUrl() . 'images/delete.png',
            'image-search'                   => $this->assetUrl() . 'images/go.png',
            'image-default_image_F'          => $this->assetUrl() . 'images/silhouette_female.png',
            'image-default_image_M'          => $this->assetUrl() . 'images/silhouette_male.png',
            'image-default_image_U'          => $this->assetUrl() . 'images/silhouette_unknown.png',
        );

        if (array_key_exists($parameter_name, $parameters)) {
            return $parameters[$parameter_name];
        } else {
            throw new \InvalidArgumentException($parameter_name);
        }
    }

    /**
     * Are there any pending changes for us to approve?
     *
     * @return bool
     */
    protected function pendingChangesExist()
    {
        return FunctionsDbPhp::i()->exists_pending_change(Auth::user(), $this->tree);
    }

    /**
     * Create a pending changes link.  Some themes prefer an alert/banner to a menu.
     *
     * @return string
     */
    protected function pendingChangesLink()
    {
        return
            '<a href="#" onclick="window.open(\'edit_changes.php\', \'_blank\', chan_window_specs); return false;">' .
            $this->pendingChangesLinkText() .
            '</a>';
    }

    /**
     * Text to use in the pending changes link.
     *
     * @return string
     */
    protected function pendingChangesLinkText()
    {
        return I18N::translate('There are pending changes for you to moderate.');
    }

    /**
     * Send any HTTP headers.
     *
     * @return void
     */
    public function sendHeaders()
    {
        header('Content-Type: text/html; charset=UTF-8');
    }

    /**
     * A list of CSS files to include for this page.
     *
     * @return string[]
     */
    protected function stylesheets()
    {
        return array();
    }

    /**
     * A fixed string to identify this theme, in settings, etc.
     *
     * @return string
     */
    abstract public function themeId();

    /**
     * What is this theme called?
     *
     * @return string
     */
    abstract public function themeName();

    /**
     * Create the <title> tag.
     *
     * @param string $title
     *
     * @return string
     */
    protected function title($title)
    {
        return '<title>' . Filter::escapeHtml($title) . '</title>';
    }
}
