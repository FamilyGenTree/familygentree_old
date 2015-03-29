<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Webtrees\LegacyBundle\Context\Application\Controller;


use FamGenTree\AppBundle\Context\GenTree\GenTreeManager;
use Fgt\Application;
use Fgt\UrlConstants;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Webtrees\LegacyBundle\Legacy\BaseController;
use Webtrees\LegacyBundle\Legacy\BaseTheme;
use Webtrees\LegacyBundle\Legacy\Output;
use Webtrees\LegacyBundle\Legacy\PageController;

class AbstractSymfonyConnectorController
{

    protected $request;
    protected $diContainer;
    protected $renderer;
    protected $outputMenus = array();
    /**
     * @var \Webtrees\LegacyBundle\Legacy\Output
     */
    protected $output = null;
    /**
     * @var BaseTheme
     */
    protected $theme;
    /**
     * @var PageController
     */
    protected $viewModel;

    function __construct(ContainerInterface $diContainer, Request $request)
    {
        $this->diContainer = $diContainer;
        $this->request     = $request;
        $this->output      = new Output();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getDiContainer()
    {
        return $this->diContainer;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $diContainer
     */
    public function setDiContainer($diContainer)
    {
        $this->diContainer = $diContainer;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

    /**
     * @return \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine
     */
    public function getTemplating()
    {
        return $this->diContainer->get('templating');
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getOutputMenus()
    {
        return $this->outputMenus;
    }

    /**
     * @return BaseTheme
     */
    protected function getTheme()
    {
        return Application::i()->getTheme();
    }

    /**
     * @return \FamGenTree\AppBundle\Context\Configuration\Domain\FgtConfig
     */
    protected function getConfig()
    {
        return $this->diContainer->get('fgt.configuration');
    }

    /**
     * @return \Webtrees\LegacyBundle\Legacy\BaseController
     */
    public function getViewModel()
    {
        return $this->viewModel;
    }

    protected function setViewModel(BaseController $param)
    {
        $this->viewModel = Application::i()->setActiveController($param);
        return $this->viewModel;
    }

    protected function redirect($url,$options=[]) {
        header('Location: ' . UrlConstants::url($url,$options));
    }

    /**
     * @param $service
     *
     * @return object
     */
    protected function get($service) {
        return $this->diContainer->get($service);
    }

    /**
     * @return GenTreeManager
     */
    protected function getGenTreeManager() {
        return $this->get('fgt.gentree.manager');
    }
}