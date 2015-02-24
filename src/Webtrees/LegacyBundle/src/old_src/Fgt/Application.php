<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Fgt;

use FamGeneTree\AppBundle\Context\Configuration\Domain\FgtConfig as FgtConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webtrees\LegacyBundle\Legacy\AdministrationTheme;
use Webtrees\LegacyBundle\Legacy\BaseController;
use Webtrees\LegacyBundle\Legacy\BaseTheme;
use Webtrees\LegacyBundle\Legacy\Filter;
use Webtrees\LegacyBundle\Legacy\WebtreesTheme;

class Application
{

    /**
     * @var Application
     */
    protected static $instance;
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
            $this->configObject = $this->diContainer->get('fgt.configuration');
        }

        return $this->configObject;
    }

    /**
     * @return BaseTheme
     */
    public function getTheme()
    {
        return $this->themeObject;
    }

    /**
     * @return \FamGeneTree\AppBundle\Service\Session
     */
    public function getSession() {
        return $this->diContainer->get('fgt.session');
    }

    /**
     *
     * @return $this
     */
    public function init()
    {
        $appInit = new AppInitializer($this->diContainer);
        $appInit->init();
        $this->initTheme();

        return $this;
    }

    /**
     * @return $this
     */
    public function shutDown()
    {
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


    public function getUrl($route, $params)
    {
        return $this->diContainer->get('router')->generate($route, $params);
    }

    private function initTheme()
    {
// Set the theme
        if (substr(WT_SCRIPT_NAME, 0, 5) === 'admin' || WT_SCRIPT_NAME === UrlConstants::MODULE_PHP && substr(Filter::get('mod_action'), 0, 5) === 'admin') {
            // Administration scripts begin with “admin” and use a special administration theme
            $this->themeObject = new AdministrationTheme($this->getSession(), Globals::i()->SEARCH_SPIDER, Globals::i()->WT_TREE);
        } else {
            $theme_id = 'webtrees';
            $this->themeObject = new WebtreesTheme($this->getSession(), Globals::i()->SEARCH_SPIDER, Globals::i()->WT_TREE);

            // Remember this setting
            Application::i()->getSession()->theme_id = $theme_id;
        }

        // These theme globals are horribly abused.
        global $bwidth, $bheight, $basexoffset, $baseyoffset, $bxspacing, $byspacing, $Dbwidth, $Dbheight;

        $bwidth      = Application::i()->getTheme()
                                  ->parameter('chart-box-x');
        $bheight     = Application::i()->getTheme()
                                  ->parameter('chart-box-y');
        $basexoffset = Application::i()->getTheme()
                                  ->parameter('chart-offset-x');
        $baseyoffset = Application::i()->getTheme()
                                  ->parameter('chart-offset-y');
        $bxspacing   = Application::i()->getTheme()
                                  ->parameter('chart-spacing-x');
        $byspacing   = Application::i()->getTheme()
                                  ->parameter('chart-spacing-y');
        $Dbwidth     = Application::i()->getTheme()
                                  ->parameter('chart-descendancy-box-x');
        $Dbheight    = Application::i()->getTheme()
                                  ->parameter('chart-descendancy-box-y');

    }

    /**
     * @return \Webtrees\LegacyBundle\Legacy\Tree
     */
    public function getTree() {
        return Globals::i()->WT_TREE;
    }

    public function getAuthService()
    {
        return $this->diContainer->get('fgt.auth');
    }

}
