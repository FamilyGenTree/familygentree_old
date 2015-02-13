<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Fgt;


use FamGeneTree\AppBundle\Context\Configuration\Domain\ConfigKeys;
use FamGeneTree\AppBundle\Context\Configuration\Domain\FgtConfig as FgtConfig;
use FamGeneTree\AppBundle\Context\Configuration\Infrastructure\ConfigRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webtrees\LegacyBundle\Legacy\AdministrationTheme;
use Webtrees\LegacyBundle\Legacy\Auth;
use Webtrees\LegacyBundle\Legacy\BaseController;
use Webtrees\LegacyBundle\Legacy\Database;
use Webtrees\LegacyBundle\Legacy\Filter;
use Webtrees\LegacyBundle\Legacy\FlashMessages;
use Webtrees\LegacyBundle\Legacy\HitCounter;
use Webtrees\LegacyBundle\Legacy\I18N;
use Webtrees\LegacyBundle\Legacy\Log;
use Webtrees\LegacyBundle\Legacy\Site;
use Webtrees\LegacyBundle\Legacy\Theme;
use Webtrees\LegacyBundle\Legacy\Tree;

class Application
{

    /**
     * @var Application
     */
    protected static $instance;
    /**
     * @var string
     */
    protected $protocol;
    /**
     * @var string
     */
    protected $host;
    /**
     * @var string
     */
    protected $port;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var \Zend_Controller_Request_Http
     */
    protected $request;
    /**
     * @var FgtConfig
     */
    protected $configObject;
    /**
     * @var ContainerInterface
     */
    protected $diContainer;
    /**
     * @var \Webtrees\LegacyBundle\Context\Application\Service\Theme
     */
    protected $themeObject;

    /**
     * Singleton protected
     */
    protected function __construct()
    {
    }

    /**
     * @return Application
     */
    public static function i()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * @return BaseController
     */
    public function getActiveController()
    {
        return BaseController::$activeController;
    }

    /**
     * @return \Doctrine\Common\Cache\CacheProvider
     */
    public function cache()
    {
        return Config::get(Config::CACHE);
    }

    /**
     * @return FgtConfig
     */
    public function getConfig()
    {
        if (null === $this->configObject) {
            $this->configObject = $this->diContainer->get('fam_gene_tree_app.configuration');
        }

        return $this->configObject;
    }

    /**
     * @return \Webtrees\LegacyBundle\Context\Application\Service\Theme
     */
    public function getTheme()
    {
        if (null === $this->themeObject) {
            $this->themeObject = $this->diContainer->get('webtrees.theme');
        }

        return $this->themeObject;
    }

    /**
     *
     * @return $this
     */
    public function init()
    {
        $this->initConfig();
        // PHP requires a time zone to be set
        date_default_timezone_set(date_default_timezone_get());
        $this->initPatchworkUtf8();
        $this->initExtCalendar();

        $this->initBaseUrl();
//        $this->initErrorHandler();

        Config::set(Config::CONFIG_PATH, dirname(dirname(dirname(dirname(dirname(dirname(__DIR__)))))) . '/data/config.ini.php');
        $this->initDatabase();
        $this->initMemoryLimit();

// The config.ini.php file must always be in a fixed location.
// Other user files can be stored elsewhere...
        Config::set(Config::DATA_DIRECTORY, realpath(Site::getPreference('INDEX_DIRECTORY')
                                                         ? Site::getPreference('INDEX_DIRECTORY')
                                                         : dirname(dirname(dirname(dirname(__DIR__)))) . '/data') . DIRECTORY_SEPARATOR);
        $this->redirectToRealUrl();

        $this->request           = new \Zend_Controller_Request_Http();
        Globals::i()->WT_REQUEST = $this->request;

        $this->initRobot();
        $this->initSession();
        $this->initTranslation();
        $this->initUserId();
        $this->initGedCom();

        // With no parameters, init() looks to the environment to choose a language
        define('WT_LOCALE', I18N::init());
        Globals::i()->WT_SESSION->locale = I18N::$locale;

// Note that the database/webservers may not be synchronised, so use DB time throughout.
        define('WT_TIMESTAMP', (int)Database::i()->prepare("SELECT UNIX_TIMESTAMP()")
                                            ->fetchOne());

// Server timezone is defined in php.ini
        define('WT_SERVER_TIMESTAMP', WT_TIMESTAMP + (int)date('Z'));

        if (Auth::check()) {
            define('WT_CLIENT_TIMESTAMP', WT_TIMESTAMP - Globals::i()->WT_SESSION->timediff);
        } else {
            define('WT_CLIENT_TIMESTAMP', WT_SERVER_TIMESTAMP);
        }
        define('WT_CLIENT_JD', 2440588 + (int)(WT_CLIENT_TIMESTAMP / 86400));


// The login URL must be an absolute URL, and can be user-defined
        if (Site::getPreference('LOGIN_URL')) {
            define('WT_LOGIN_URL', Site::getPreference('LOGIN_URL'));
        } else {
            define('WT_LOGIN_URL', UrlConstants::url(UrlConstants::LOGIN_PHP));
            $this->getConfig()->set('WT_LOGIN_URL', UrlConstants::url(UrlConstants::LOGIN_PHP), FgtConfig::SCOPE_SITE);
        }


// If there is no current tree and we need one, then redirect somewhere
        if (!in_array(WT_SCRIPT_NAME, array(
            UrlConstants::ADMIN_TREES_MERGE_PHP,
            UrlConstants::ADMIN_PGV_TO_WT_PHP,
            UrlConstants::LOGIN_PHP,
            UrlConstants::LOGOUT_PHP,
            UrlConstants::IMPORT_PHP,
            UrlConstants::HELP_TEXT_PHP,
            UrlConstants::MESSAGE_PHP
        ))
        ) {
            if (!isset(Globals::i()->WT_TREE) || !Globals::i()->WT_TREE->getPreference('imported')) {
                if (Auth::isAdmin()) {
                    header('Location: ' . Config::get(Config::BASE_URL) . 'admin_trees_manage.php');
                } else {
                    header('Location: ' . WT_LOGIN_URL . '?url=' . rawurlencode(WT_SCRIPT_NAME . (isset($_SERVER['QUERY_STRING'])
                                                                                    ? '?' . $_SERVER['QUERY_STRING']
                                                                                    : '')), true, 301);

                }
                exit;
            }
        }

// Update the login time every 5 minutes
        if (WT_TIMESTAMP - Globals::i()->WT_SESSION->activity_time > 300) {
            Auth::user()
                ->setPreference('sessiontime', WT_TIMESTAMP);
            Globals::i()->WT_SESSION->activity_time = WT_TIMESTAMP;
        }
        $this->initTheme();


// Page hit counter - load after theme, as we need theme formatting
        if (Globals::i()->WT_TREE && Globals::i()->WT_TREE->getPreference('SHOW_COUNTER') && !Globals::i()->SEARCH_SPIDER) {
            HitCounter::setup();
        } else {
            Globals::i()->hitCount = '';
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function shutDown()
    {
        \Zend_Session::writeClose();

        return $this;
    }

    /**
     * @return $this
     */
    public function started()
    {
// Keep track of time statistics, for the summary in the footer
        define('WT_START_TIME', microtime(true));

        return $this;
    }

    /**
     * @return $this
     */
    public function stopped()
    {
        return $this;
    }

    /**
     * @param \Webtrees\LegacyBundle\Legacy\BaseController $param
     *
     * @return \Webtrees\LegacyBundle\Legacy\BaseController
     */
    public function setActiveController(BaseController $param)
    {
        BaseController::$activeController = $param;

        return $this->getActiveController();
    }

    public function setDiController(ContainerInterface $container)
    {
        $this->diContainer = $container;
    }

    protected function initErrorHandler()
    {
// Log errors to the database
        set_error_handler(function ($errno, $errstr) {
            static $first_error = false;

            if (!$first_error) {
                $first_error = true;

                $message = 'ERROR ' . $errno . ': ' . $errstr;
                // Although debug_backtrace should always exist, PHP sometimes crashes without this check.
                if (function_exists('debug_backtrace') && strstr($errstr, 'headers already sent by') === false) {
                    $backtraces = debug_backtrace();
                    foreach ($backtraces as $level => $backtrace) {
                        if ($level === 0) {
                            $message .= '; Error occurred on ';
                        } else {
                            $message .= '; called from ';
                        }
                        if (isset($backtrace[$level]['line']) && isset($backtrace[$level]['file'])) {
                            $message .= 'line ' . $backtraces[$level]['line'] . ' of file ' . $backtraces[$level]['file'];
                        }
                        if ($level < count($backtraces) - 1) {
                            $message .= ' in function ' . $backtraces[$level + 1]['function'];
                        }
                    }
                }
                Log::addErrorLog($message);
            }

            return false;
        });
    }

    /**
     * Calculate the base URL, so we can generate absolute URLs.
     */
    protected function initBaseUrl()
    {
        $this->protocol = Filter::server('HTTP_X_FORWARDED_PROTO', 'https?', Filter::server('HTTPS', null, 'off') === 'off'
            ? 'http'
            : 'https');

// For CLI scripts, use localhost.
        $this->host = Filter::server('SERVER_NAME', null, 'localhost');

        $this->port = Filter::server('HTTP_X_FORWARDED_PORT', '80|443', Filter::server('SERVER_PORT', null, '80'));

// Ignore the default port.
        if ($this->protocol === 'http' && $this->port === '80' || $this->protocol === 'https' && $this->port === '443') {
            $this->port = '';
        } else {
            $this->port = ':' . $this->port;
        }

// REDIRECT_URL should be set when Apache is following a RedirectRule
// PHP_SELF may have trailing path: /path/to/script.php/FOO/BAR
        $this->path = Filter::server('REDIRECT_URL', null, Filter::server('PHP_SELF'));
        $path       = substr($this->path, 0, stripos($this->path, WT_SCRIPT_NAME));

        Config::set(Config::BASE_URL, $this->protocol . '://' . $this->host . $this->port . $path . '/');

    }

    protected function initPatchworkUtf8()
    {
// Use the patchwork/utf8 library to:
// 1) set all PHP defaults to UTF-8
// 2) create shims for missing mb_string functions such as mb_strlen()
// 3) check that requests are valid UTF-8
        \Patchwork\Utf8\Bootup::initAll(); // Enables the portablity layer and configures PHP for UTF-8
        \Patchwork\Utf8\Bootup::filterRequestUri(); // Redirects to an UTF-8 encoded URL if it's not already the case
        \Patchwork\Utf8\Bootup::filterRequestInputs(); // Normalizes HTTP inputs to UTF-8 NFC
    }

    protected function initExtCalendar()
    {
// Use the fisharebest/ext-calendar library to
// 1) provide shims for the PHP ext/calendar extension, such as JewishToJD()
// 2) provide calendar conversions for the Arabic and Persian calendars
        \Fisharebest\ExtCalendar\Shim::create();
    }

    private function initDatabase()
    {
// Connect to the database
        try {
            if (!Database::i()->isConnected()) {
                Database::i()->createInstance(
                    $this->container->getParameter('database_host'),
                    $this->container->getParameter('database_port'),
                    $this->container->getParameter('database_name'),
                    $this->container->getParameter('database_user'),
                    $this->container->getParameter('database_password'),
                    $this->container->getParameter('database_prefix')
                );
            }
            unset($dbConfig);
            // Some of the FAMILY JOIN HUSBAND JOIN WIFE queries can excede the MAX_JOIN_SIZE setting
            Database::i()->exec("SET NAMES 'utf8' COLLATE 'utf8_unicode_ci', SQL_BIG_SELECTS=1");
            Database::i()->updateSchema(WT_ROOT . 'includes/db_schema/', 'WT_SCHEMA_VERSION', WT_SCHEMA_VERSION);
        } catch (\PDOException $ex) {
            FlashMessages::addMessage($ex->getMessage(), 'danger');
            header('Location: ' . UrlConstants::url(UrlConstants::SITE_UNAVAILABLE_PHP));
            throw $ex;
        }

    }

    private function initMemoryLimit()
    {
        // Request more resources - if we can/want to
        if (!ini_get('safe_mode')) {
            $memory_limit = Site::getPreference('MEMORY_LIMIT');
            if ($memory_limit && strpos(ini_get('disable_functions'), 'ini_set') === false) {
                ini_set('memory_limit', $memory_limit);
            }
            $max_execution_time = Site::getPreference('MAX_EXECUTION_TIME');
            if ($max_execution_time && strpos(ini_get('disable_functions'), 'set_time_limit') === false) {
                set_time_limit($max_execution_time);
            }
        }
    }

    private function redirectToRealUrl()
    {
        // If we have a preferred URL (e.g. www.example.com instead of www.isp.com/~example), then redirect to it.
        $SERVER_URL = Site::getPreference('SERVER_URL');
        if ($SERVER_URL && $SERVER_URL != Config::get(Config::BASE_URL)) {
            header('Location: ' . $SERVER_URL . WT_SCRIPT_NAME . (isset($_SERVER['QUERY_STRING'])
                                                                                 ? '?' . $_SERVER['QUERY_STRING']
                                                                                 : ''), true, 301);
            exit;
        }
    }

    private function initRobot()
    {
        $rule = Database::i()->prepare(
            "SELECT SQL_CACHE rule FROM `##site_access_rule`" .
            " WHERE IFNULL(INET_ATON(?), 0) BETWEEN ip_address_start AND ip_address_end" .
            " AND ? LIKE user_agent_pattern" .
            " ORDER BY ip_address_end LIMIT 1"
        )
                        ->execute(array(
                                      Globals::i()->WT_REQUEST->getClientIp(),
                                      Filter::server('HTTP_USER_AGENT')
                                  ))
                        ->fetchOne();

        switch ($rule) {
            case 'allow':
                Globals::i()->SEARCH_SPIDER = false;
                break;
            case 'deny':
                http_response_code(403);
                exit;
            case 'robot':
            case 'unknown':
                // Search engines don’t send cookies, and so create a new session with every visit.
                // Make sure they always use the same one
                \Zend_Session::setId('search-engine-' . str_replace('.', '-', Globals::i()->WT_REQUEST->getClientIp()));
                Globals::i()->SEARCH_SPIDER = true;
                break;
            case '':
                Database::i()->prepare(
                    "INSERT INTO `##site_access_rule` (ip_address_start, ip_address_end, user_agent_pattern, comment) VALUES (IFNULL(INET_ATON(?), 0), IFNULL(INET_ATON(?), 4294967295), ?, '')"
                )
                        ->execute(array(
                                      Globals::i()->WT_REQUEST->getClientIp(),
                                      Globals::i()->WT_REQUEST->getClientIp(),
                                      Filter::server('HTTP_USER_AGENT', null, '')
                                  ));
                Globals::i()->SEARCH_SPIDER = true;
                break;
        }

        if (Globals::i()->SEARCH_SPIDER
            && !in_array(WT_SCRIPT_NAME, array(
                UrlConstants::INDEX_PHP,
                UrlConstants::INDILIST_PHP,
                UrlConstants::MODULE_PHP,
                UrlConstants::MEDIAFIREWALL_PHP,
                UrlConstants::INDIVIDUAL_PHP,
                UrlConstants::FAMILY_PHP,
                UrlConstants::MEDIAVIEWER_PHP,
                UrlConstants::NOTE_PHP,
                UrlConstants::REPO_PHP,
                UrlConstants::SOURCE_PHP,
            ))
        ) {
            http_response_code(403);
            echo '<h1>Search engine</h1><p class="ui-state-error">You do not have permission to view this page.</p>';
            exit;
        }

    }

    private function initSession()
    {
// Store our session data in the database.
        session_set_save_handler(
// open
            function () {
                return true;
            },
            // close
            function () {
                return true;
            },
            // read
            function ($id) {
                return Database::i()->prepare("SELECT session_data FROM `##session` WHERE session_id=?")
                               ->execute(array($id))
                               ->fetchOne();
            },
            // write
            function ($id, $data) {
                // Only update the session table once per minute, unless the session data has actually changed.
                Database::i()->prepare(
                    "INSERT INTO `##session` (session_id, user_id, ip_address, session_data, session_time)" .
                    " VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP - SECOND(CURRENT_TIMESTAMP))" .
                    " ON DUPLICATE KEY UPDATE" .
                    " user_id      = VALUES(user_id)," .
                    " ip_address   = VALUES(ip_address)," .
                    " session_data = VALUES(session_data)," .
                    " session_time = CURRENT_TIMESTAMP - SECOND(CURRENT_TIMESTAMP)"
                )
                        ->execute(array(
                                      $id,
                                      (int)Auth::id(),
                                      Globals::i()->WT_REQUEST->getClientIp(),
                                      $data
                                  ));

                return true;
            },
            // destroy
            function ($id) {
                Database::i()->prepare("DELETE FROM `##session` WHERE session_id=?")
                        ->execute(array($id));

                return true;
            },
            // gc
            function ($maxlifetime) {
                Database::i()
                        ->prepare("DELETE FROM `##session` WHERE session_time < DATE_SUB(NOW(), INTERVAL ? SECOND)")
                        ->execute(array($maxlifetime));

                return true;
            }
        );

// Use the Zend_Session object to start the session.
// This allows all the other Zend Framework components to integrate with the session
        define('WT_SESSION_NAME', 'WT_SESSION');
        $cfg = array(
            'name'            => WT_SESSION_NAME,
            'cookie_lifetime' => 0,
            'gc_maxlifetime'  => Site::getPreference('SESSION_TIME'),
            'gc_probability'  => 1,
            'gc_divisor'      => 100,
            'cookie_path'     => parse_url(Config::get(Config::BASE_URL), PHP_URL_PATH),
            'cookie_httponly' => true,
        );

        \Zend_Session::start($cfg);

// Register a session “namespace” to store session data.  This is better than
// using $_SESSION, as we can avoid clashes with other modules or applications,
// and problems with servers that have enabled “register_globals”.
        Globals::i()->WT_SESSION = new \Zend_Session_Namespace('WEBTREES');

        if (!Globals::i()->SEARCH_SPIDER && !Globals::i()->WT_SESSION->initiated) {
            // A new session, so prevent session fixation attacks by choosing a new PHPSESSID.
            \Zend_Session::regenerateId();
            Globals::i()->WT_SESSION->initiated = true;
        } else {
            // An existing session
        }
    }

    private function initUserId()
    {
        /** @deprecated Will be removed in 1.7.0 */
        define('WT_USER_ID', Auth::id());
        /** @deprecated Will be removed in 1.7.0 */
        define('WT_USER_NAME', Auth::id() ? Auth::user()
                                                ->getUserName() : '');
    }

    private function initGedCom()
    {

// Set the active GEDCOM
        $gedcom = null;
        if (isset($_REQUEST['ged'])) {
            // .... from the URL or form action
            $gedcom = $_REQUEST['ged'];
        } elseif (!empty(Globals::i()->WT_SESSION->GEDCOM)) {
            // .... the most recently used one
            $gedcom = Globals::i()->WT_SESSION->GEDCOM;
        } else {
            // Try the site default
            $gedcom = Site::getPreference('DEFAULT_GEDCOM');
        }
// Choose the selected tree (if it exists), or any valid tree otherwise
        Globals::i()->WT_TREE = null;
        foreach (Tree::getAll() as $tree) {
            Globals::i()->WT_TREE = $tree;
            if (Globals::i()->WT_TREE->getName() == (isset($gedcom) ? $gedcom : null)
                && (Globals::i()->WT_TREE->getPreference('imported') || Auth::isAdmin())
            ) {
                break;
            }
        }

// These attributes of the currently-selected tree are used frequently
        if (Globals::i()->WT_TREE) {
            define('WT_GEDCOM', Globals::i()->WT_TREE->getName());
            define('WT_GED_ID', Globals::i()->WT_TREE->getTreeId());
            define('WT_GEDURL', Globals::i()->WT_TREE->getNameUrl());
            define('WT_TREE_TITLE', Globals::i()->WT_TREE->getTitleHtml());
            define('WT_USER_GEDCOM_ADMIN', Auth::isManager(Globals::i()->WT_TREE));
            define('WT_USER_CAN_ACCEPT', Auth::isModerator(Globals::i()->WT_TREE));
            define('WT_USER_CAN_EDIT', Auth::isEditor(Globals::i()->WT_TREE));
            define('WT_USER_CAN_ACCESS', Auth::isMember(Globals::i()->WT_TREE));
            define('WT_USER_GEDCOM_ID', Globals::i()->WT_TREE->getUserPreference(Auth::user(), 'gedcomid'));
            define('WT_USER_ROOT_ID', Globals::i()->WT_TREE->getUserPreference(Auth::user(), 'rootid')
                ? Globals::i()->WT_TREE->getUserPreference(Auth::user(), 'rootid') : WT_USER_GEDCOM_ID);
            define('WT_USER_PATH_LENGTH', Globals::i()->WT_TREE->getUserPreference(Auth::user(), 'RELATIONSHIP_PATH_LENGTH'));
            if (WT_USER_GEDCOM_ADMIN) {
                define('WT_USER_ACCESS_LEVEL', WT_PRIV_NONE);
            } elseif (WT_USER_CAN_ACCESS) {
                define('WT_USER_ACCESS_LEVEL', WT_PRIV_USER);
            } else {
                define('WT_USER_ACCESS_LEVEL', WT_PRIV_PUBLIC);
            }
        } else {
            define('WT_GEDCOM', '');
            define('WT_GED_ID', null);
            define('WT_GEDURL', '');
            define('WT_TREE_TITLE', WT_WEBTREES);
            define('WT_USER_GEDCOM_ADMIN', false);
            define('WT_USER_CAN_ACCEPT', false);
            define('WT_USER_CAN_EDIT', false);
            define('WT_USER_CAN_ACCESS', false);
            define('WT_USER_GEDCOM_ID', '');
            define('WT_USER_ROOT_ID', '');
            define('WT_USER_PATH_LENGTH', 0);
            define('WT_USER_ACCESS_LEVEL', WT_PRIV_PUBLIC);
        }
        Globals::i()->GEDCOM = WT_GEDCOM;
// Set our gedcom selection as a default for the next page
        Globals::i()->WT_SESSION->GEDCOM = WT_GEDCOM;

    }

    private function initTranslation()
    {
        I18N::init();
    }

    private function initTheme()
    {
// Set the theme
        if (substr(WT_SCRIPT_NAME, 0, 5) === 'admin' || WT_SCRIPT_NAME === UrlConstants::MODULE_PHP && substr(Filter::get('mod_action'), 0, 5) === 'admin') {
            // Administration scripts begin with “admin” and use a special administration theme
            Theme::theme(new AdministrationTheme())
                 ->init(Globals::i()->WT_SESSION, Globals::i()->SEARCH_SPIDER, Globals::i()->WT_TREE);
        } else {
            if (Site::getPreference('ALLOW_USER_THEMES')) {
                // Requested change of theme?
                $theme_id = Filter::get('theme');
                if (!array_key_exists($theme_id, Theme::themeNames())) {
                    $theme_id = '';
                }
                // Last theme used?
                if (!$theme_id && array_key_exists(Globals::i()->WT_SESSION->theme_id, Theme::themeNames())) {
                    $theme_id = Globals::i()->WT_SESSION->theme_id;
                }
            } else {
                $theme_id = '';
            }
            if (!$theme_id) {
                // User cannot choose (or has not chosen) a theme.
                // 1) gedcom setting
                // 2) site setting
                // 3) webtrees
                // 4) first one found
                if (WT_GED_ID) {
                    $theme_id = Globals::i()->WT_TREE->getPreference('THEME_DIR');
                }
                if (!array_key_exists($theme_id, Theme::themeNames())) {
                    $theme_id = Site::getPreference('THEME_DIR');
                }
                if (!array_key_exists($theme_id, Theme::themeNames())) {
                    $theme_id = 'webtrees';
                }
            }
            foreach (Theme::installedThemes() as $theme) {
                if ($theme->themeId() === $theme_id) {
                    Theme::theme($theme)
                         ->init(Globals::i()->WT_SESSION, Globals::i()->SEARCH_SPIDER, Globals::i()->WT_TREE);
                }
            }

            // Remember this setting
            Globals::i()->WT_SESSION->theme_id = $theme_id;
        }

        // These theme globals are horribly abused.
        global $bwidth, $bheight, $basexoffset, $baseyoffset, $bxspacing, $byspacing, $Dbwidth, $Dbheight;

        $bwidth      = Theme::theme()
                            ->parameter('chart-box-x');
        $bheight     = Theme::theme()
                            ->parameter('chart-box-y');
        $basexoffset = Theme::theme()
                            ->parameter('chart-offset-x');
        $baseyoffset = Theme::theme()
                            ->parameter('chart-offset-y');
        $bxspacing   = Theme::theme()
                            ->parameter('chart-spacing-x');
        $byspacing   = Theme::theme()
                            ->parameter('chart-spacing-y');
        $Dbwidth     = Theme::theme()
                            ->parameter('chart-descendancy-box-x');
        $Dbheight    = Theme::theme()
                            ->parameter('chart-descendancy-box-y');

    }

    private function initConfig()
    {
        $config = $this->getConfig();
        $config->set(ConfigKeys::SYSTEM_PATH_DATA, Config::get(Config::DATA_DIRECTORY), FgtConfig::SCOPE_RUNTIME);
        $config->set(ConfigKeys::SYSTEM_PATH_CONFIG, Config::get(Config::CONFIG_PATH), FgtConfig::SCOPE_RUNTIME);
        $config->set(ConfigKeys::SYSTEM_CACHE_DIR, Config::get(Config::CACHE_DIR), FgtConfig::SCOPE_RUNTIME);
        $config->set(ConfigKeys::SYSTEM_CACHE, Config::get(Config::CACHE), FgtConfig::SCOPE_RUNTIME);
        $config->set(ConfigKeys::SYSTEM_MODULES_PATH, Config::get(Config::MODULES_DIR), FgtConfig::SCOPE_RUNTIME);

        Constants::defineCommonConstants($this->getConfig());
    }
}