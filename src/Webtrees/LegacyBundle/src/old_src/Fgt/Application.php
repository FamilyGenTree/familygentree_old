<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Fgt;

use FamGeneTree\AppBundle\Context\Configuration\Domain\FgtConfig as FgtConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webtrees\LegacyBundle\Legacy\BaseController;

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
}