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

use FamGeneTree\AppBundle\Context\Configuration\Domain\ConfigKeys;
use Fgt\Application;
use Fgt\Globals;

/**
 * Class PageController Controller for full-page, themed HTML responses
 */
class PageController extends BaseController implements PageControllerInterface
{
    const VIEW_STYLE_NONE   = '';
    const VIEW_STYLE_SIMPLE = 'simple';
    protected $viewStyle;

    // Page header information
    private $canonical_url = '';
    private $page_title    = null; // <head><title> $page_title </title></head>
    private $meta          = array();

    /**
     * Startup activity
     */
    public function __construct(
        \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine $templateEngine,
        $template = 'WebtreesLegacyThemeBundle:Default:index.html.twig'
    ) {
        parent::__construct($templateEngine, $template);
        $fgtConfig        = Application::i()->getConfig();
        $this->page_title = $fgtConfig->getValue(ConfigKeys::SYSTEM_NAME);
        $this->addMeta('robots', $fgtConfig->getValue(ConfigKeys::SITE_META_ROBOTS));
        $this->addMeta('generator', $fgtConfig->getValue(ConfigKeys::SYSTEM_NAME)
                                    . ' ' . $fgtConfig->getValue(ConfigKeys::SYSTEM_VERSION)
                                    . ' - ' . $fgtConfig->getValue(ConfigKeys::PROJECT_HOMEPAGE_URL));

        if (Application::i()->getTree()) {
            $this->metaDescription(Application::i()->getTree()->getPreference('META_DESCRIPTION'));
        }

        // Every page uses these scripts
        $this
            ->addExternalJavascript($fgtConfig->getValue('WT_JQUERY_JS_URL'))
            ->addExternalJavascript($fgtConfig->getValue('WT_JQUERYUI_JS_URL'))
            ->addExternalJavascript(Application::i()
                                               ->getConfig()
                                               ->getValue('WT_STATIC_URL') . WebtreesTheme::WT_WEBTREES_JS_URL);
    }

    /**
     * What should this page show in the browser’s title bar?
     *
     * @param string $page_title
     *
     * @return $this
     */
    public function setPageTitle($page_title)
    {
        $this->page_title = $page_title;

        return $this;
    }

    /**
     * Some pages will want to display this as <h2> $page_title </h2>
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->page_title;
    }

    /**
     * What is the preferred URL for this page?
     *
     * @param $canonical_url
     *
     * @return $this
     */
    public function setCanonicalUrl($canonical_url)
    {
        $this->canonical_url = $canonical_url;

        return $this;
    }

    /**
     * What is the preferred URL for this page?
     *
     * @return string
     */
    public function getCanonicalUrl()
    {
        return $this->canonical_url;
    }

    /**
     * Should robots index this page?
     *
     * @param string $meta_robots
     *
     * @return $this
     */
    public function setMetaRobots($meta_robots)
    {
        $this->addMeta('robots', $meta_robots);

        return $this;
    }

    /**
     * Should robots index this page?
     *
     * @return string
     */
    public function getMetaRobots()
    {
        return $this->getMeta('robots');
    }

    /**
     * Restrict access
     *
     * @param boolean $condition
     *
     * @return $this
     */
    public function restrictAccess($condition)
    {
        if ($condition !== true) {
            header('Location: ' . WT_LOGIN_URL . '?url=' . rawurlencode(Functions::i()->get_query_url()));
            exit;
        }

        return $this;
    }

    /**
     * Print the page footer, using the theme
     *
     * @return string
     */
    protected function pageFooter()
    {
        return
            Application::i()->getTheme()
                       ->footerContainer() .
            $this->getJavascript() .
            Application::i()->getTheme()
                       ->hookFooterExtraJavascript() .
            '</body>' .
            '</html>' . PHP_EOL;
    }

    /**
     * Print the page header, using the theme
     *
     * @param string $view 'simple' or ''
     *
     * @return $this
     */
    public function pageHeader($view = '')
    {
        $this->setViewStyle($view);
        // Give Javascript access to some PHP constants
        $this->addInlineJavascript('
			var WT_STATIC_URL  = "' . Filter::escapeJs(WT_STATIC_URL) . '";
			var WT_MODULES_DIR = "' . Filter::escapeJs(WT_MODULES_DIR) . '";
			var WT_GEDCOM      = "' . Filter::escapeJs(WT_GEDCOM) . '";
			var WT_GED_ID      = "' . Filter::escapeJs(WT_GED_ID) . '";
			var textDirection  = "' . Filter::escapeJs(Globals::i()->TEXT_DIRECTION) . '";
			var WT_SCRIPT_NAME = "' . Filter::escapeJs(WT_SCRIPT_NAME) . '";
			var WT_LOCALE      = "' . Filter::escapeJs(WT_LOCALE) . '";
			var WT_CSRF_TOKEN  = "' . Filter::escapeJs(Filter::getCsrfToken()) . '";
		', static::JS_PRIORITY_HIGH);

        // Temporary fix for access to main menu hover elements on android/blackberry touch devices
        $this->addInlineJavascript('
			if(navigator.userAgent.match(/Android|PlayBook/i)) {
				jQuery(".primary-menu > li > a").attr("href", "#");
			}
		');

        // We've displayed the header - display the footer automatically
        $this->page_header = true;

        return $this;
    }

    /**
     * Get significant information from this page, to allow other pages such as
     * charts and reports to initialise with the same records
     *
     * @return Individual
     */
    public function getSignificantIndividual()
    {
        static $individual; // Only query the DB once.

        if (!$individual && WT_USER_ROOT_ID) {
            $individual = Individual::getInstance(WT_USER_ROOT_ID);
        }
        if (!$individual && WT_USER_GEDCOM_ID) {
            $individual = Individual::getInstance(WT_USER_GEDCOM_ID);
        }
        if (!$individual) {
            $individual = Individual::getInstance(Globals::i()->WT_TREE->getPreference('PEDIGREE_ROOT_ID'));
        }
        if (!$individual) {
            $individual = Individual::getInstance(
                Database::i()->prepare(
                    "SELECT MIN(i_id) FROM `##individuals` WHERE i_file=?"
                )
                        ->execute(array(WT_GED_ID))
                        ->fetchOne()
            );
        }
        if (!$individual) {
            // always return a record
            $individual = new Individual('I', '0 @I@ INDI', null, WT_GED_ID);
        }

        return $individual;
    }

    /**
     * Get significant information from this page, to allow other pages such as
     * charts and reports to initialise with the same records
     *
     * @return Family
     */
    public function getSignificantFamily()
    {
        $individual = $this->getSignificantIndividual();
        if ($individual) {
            foreach ($individual->getChildFamilies() as $family) {
                return $family;
            }
            foreach ($individual->getSpouseFamilies() as $family) {
                return $family;
            }
        }

        // always return a record
        return new Family('F', '0 @F@ FAM', null, WT_GED_ID);
    }

    /**
     * Get significant information from this page, to allow other pages such as
     * charts and reports to initialise with the same records
     *
     * @return string
     */
    public function getSignificantSurname()
    {
        return '';
    }

    public function render($templateName = null, array $arguments = array())
    {
        $arguments = array_merge(
            array(
                'page_title'     => $this->getPageTitle(),
                'metaRobots'     => $this->getMetaRobots(),
                'canonicalUrl'   => $this->getCanonicalUrl(),
                'powered_by_url' => Application::i()->getConfig()->getValue(ConfigKeys::PROJECT_HOMEPAGE_URL),
                'debug' => array(
                    'execution_time' => I18N::number(microtime(true) - WT_START_TIME, 3) . ' seconds',
                    'memory' => I18N::number(memory_get_peak_usage(true) / 1024) . ' KB',
                    'sql_queries' => I18N::number(Database::i()->getQueryCount())
                )
            ),
            $arguments
        );

        return parent::render($templateName, $arguments);
    }

    private function setViewStyle($view)
    {
        $this->viewStyle = $view;
    }

    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;
    }

    public function getMeta($key)
    {
        return $this->meta[$key];
    }

    /**
     * Create the <link rel="canonical"> tag.
     *
     * @param string $url
     *
     * @return string
     */
    protected function metaCanonicalUrl($url)
    {
        if ($url) {
            return '<link rel="canonical" href="' . $url . '">';
        } else {
            return '';
        }
    }

    /**
     * Create the <meta charset=""> tag.
     *
     * @return string
     */
    protected function metaCharset()
    {
        return '<meta charset="UTF-8">';
    }

    /**
     * Create the <meta name="description"> tag.
     *
     * @param string $description
     *
     * @return string
     */
    protected function metaDescription($description)
    {
        $this->addMeta('description', $description);
    }

    /**
     * Create the <meta name="generator"> tag.
     *
     * @param string $generator
     *
     * @return string
     */
    protected function metaGenerator($generator)
    {
        $this->addMeta('generator', $generator);
    }

    /**
     * Create the <meta http-equiv="X-UA-Compatible"> tag.
     *
     * @return string
     */
    protected function metaUaCompatible()
    {

        return '';
    }

}
