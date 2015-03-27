<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Context\Setup\Step;

use FamGenTree\SetupBundle\Context\Setup\Config\ConfigAbstract;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class StepBase extends ContainerAware
{
    protected $results = [];
    protected $config  = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param \FamGenTree\SetupBundle\Context\Setup\Config\ConfigAbstract $config
     *
     * @return StepResultAggregate|null
     */
    public function checkConfig(ConfigAbstract $config)
    {
        return null;
    }

    abstract public function run();

    /**
     * @param ConfigAbstract|null $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return StepResult[]
     */
    public function getResults()
    {
        return $this->results;
    }

    public function isSuccess()
    {
        $ret = true;
        foreach ($this->getResults() as $stepResult) {
            $ret = $ret && $stepResult->isSuccess();
        }

        return $ret;
    }

    /**
     * @return ConfigAbstract|null
     */
    protected function getConfig()
    {
        return $this->config;
    }

    protected function addResult(StepResult $result)
    {
        $this->results[] = $result;

        return $this;
    }

    protected function createSuccessResultAggregate($name)
    {
        return new StepResultAggregate(
            $name
        );
    }

    /**
     * @return \FamGenTree\SetupBundle\Context\Setup\SetupManager
     */
    protected function getSetupManager()
    {
        return $this->container->get('fgt.setup.manager');
    }
}