<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Step;

use FamGeneTree\SetupBundle\Context\Setup\Config\ConfigAbstract;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class StepBase extends ContainerAware
{
    protected $results = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param \FamGeneTree\SetupBundle\Context\Setup\Config\ConfigAbstract $config
     *
     * @return StepResultAggregate
     */
    abstract public function checkConfig(ConfigAbstract $config);

    /**
     *
     */
    public function getResults()
    {
        return $this->results;
    }

    protected function addResult(StepResult $result)
    {
        $this->results[] = $result;

        return $this;
    }
}