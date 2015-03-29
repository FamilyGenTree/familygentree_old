<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Fgt;


use FamGenTree\AppBundle\Context\Configuration\Domain\FgtConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webtrees\LegacyBundle\Legacy\Site;

use FamGenTree\AppBundle\Context\Configuration\Domain\ConfigKeys;
use Webtrees\LegacyBundle\Legacy\Auth;
use Webtrees\LegacyBundle\Legacy\Database;
use Webtrees\LegacyBundle\Legacy\Filter;
use Webtrees\LegacyBundle\Legacy\FlashMessages;
use Webtrees\LegacyBundle\Legacy\HitCounter;
use Webtrees\LegacyBundle\Legacy\I18N;
use Webtrees\LegacyBundle\Legacy\Log;
use Webtrees\LegacyBundle\Legacy\Tree;

class AppInitializer
{
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
     * @var FgtConfig
     */
    protected $configObject;
    /**
     * @var ContainerInterface
     */
    protected $diContainer;

    public function __construct($diContainer)
    {
        $this->diContainer = $diContainer;
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
        Globals::i()->WT_REQUEST = $this->diContainer->get('request');

        $this->initRobot();
        $this->initSession();
        $this->initTranslation();
        $this->initUserId();
        $this->initGedCom();

        // With no parameters, init() looks to the environment to choose a language
        define('WT_LOCALE', I18N::init());
        Application::i()->getSession()->locale = I18N::$locale;

// Note that the database/webservers may not be synchronised, so use DB time throughout.
        define('WT_TIMESTAMP', (int)Database::i()->prepare("SELECT UNIX_TIMESTAMP()")
                                            ->fetchOne());

// Server timezone is defined in php.ini
        define('WT_SERVER_TIMESTAMP', WT_TIMESTAMP + (int)date('Z'));

        if (Auth::check()) {
            define('WT_CLIENT_TIMESTAMP', WT_TIMESTAMP - Application::i()->getSession()->timediff);
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
            UrlConstants::ADMIN_TREES_MANAGE_PHP,
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
                    $url = UrlConstants::url(UrlConstants::ADMIN_TREES_MANAGE_PHP);
                    header('Location: ' . $url);
                } else {
                    header('Location: ' . WT_LOGIN_URL . '?url=' . rawurlencode(WT_SCRIPT_NAME . (isset($_SERVER['QUERY_STRING'])
                                                                                    ? '?' . $_SERVER['QUERY_STRING']
                                                                                    : '')), true, 301);

                }
                exit;
            }
        }

// Update the login time every 5 minutes
        if (WT_TIMESTAMP - Application::i()->getSession()->activity_time > 300) {
            Auth::user()
                ->setPreference('sessiontime', WT_TIMESTAMP);
            Application::i()->getSession()->activity_time = WT_TIMESTAMP;
        }

// Page hit counter - load after theme, as we need theme formatting
        if (isset(Globals::i()->WT_TREE) && Globals::i()->WT_TREE->getPreference('SHOW_COUNTER') && !Globals::i()->SEARCH_SPIDER) {
            HitCounter::setup();
        } else {
            Globals::i()->hitCount = '';
        }

        return $this;
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
        } elseif (!empty(Application::i()->getSession()->GEDCOM)) {
            // .... the most recently used one
            $gedcom = Application::i()->getSession()->GEDCOM;
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
        if (isset(Globals::i()->WT_TREE) && Globals::i()->WT_TREE) {
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
        Application::i()->getSession()->GEDCOM = WT_GEDCOM;

    }

    private function initTranslation()
    {
        I18N::init();
    }

    private function initConfig()
    {
        $config = $this->getConfig();
        $config->set(ConfigKeys::SITE_PATH_DATA, Config::get(Config::DATA_DIRECTORY), FgtConfig::SCOPE_RUNTIME);
        $config->set(ConfigKeys::SYSTEM_PATH_CONFIG, Config::get(Config::CONFIG_PATH), FgtConfig::SCOPE_RUNTIME);
        $config->set(ConfigKeys::SYSTEM_CACHE_DIR, Config::get(Config::CACHE_DIR), FgtConfig::SCOPE_RUNTIME);
        $config->set(ConfigKeys::SYSTEM_CACHE, Config::get(Config::CACHE), FgtConfig::SCOPE_RUNTIME);
        $config->set(ConfigKeys::SYSTEM_MODULES_PATH, Config::get(Config::MODULES_DIR), FgtConfig::SCOPE_RUNTIME);

        Constants::defineCommonConstants($this->getConfig());
    }

    public function getConfig()
    {
        if (null === $this->configObject) {
            $this->configObject = $this->diContainer->get('fgt.configuration');
        }

        return $this->configObject;
    }
}