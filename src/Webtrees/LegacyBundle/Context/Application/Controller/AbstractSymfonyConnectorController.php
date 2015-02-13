<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Webtrees\LegacyBundle\Context\Application\Controller;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Webtrees\LegacyBundle\Legacy\BaseTheme;
use Webtrees\LegacyBundle\Legacy\Theme;

class AbstractSymfonyConnectorController
{

    protected $request;
    protected $diContainer;
    protected $renderer;
    protected $output = array();
    protected $outputMenus = array();
    /**
     * @var BaseTheme
     */
    protected $theme;

    function __construct(ContainerInterface $diContainer, Request $request)
    {
        $this->diContainer = $diContainer;
        $this->request     = $request;
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
    public function getTemplating() {
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
    protected function getTheme() {
        if (null === $this->theme) {


            $this->theme = $this->diContainer->get('webtrees.theme');
        }
        return $this->theme;
    }

    /**
     * @return \FamGeneTree\AppBundle\Context\Configuration\Domain\FgtConfig
     */
    protected function getConfig() {
        return $this->diContainer->get('fam_gene_tree_app.configuration');
    }
}