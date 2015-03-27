<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck;

use FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\ValueObject\PreRequirementResult;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class CheckAbstract extends ContainerAware implements CheckInterface
{
    protected $results;
    protected $name;
    protected $description;

    public function __construct(ContainerInterface $container, $name, $description)
    {
        $this->container   = $container;
        $this->name        = $name;
        $this->description = $description;
        $this->results     = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        return $this->results;
    }

    public function isPassed()
    {
        $passed = true;
        /** @var PreRequirementResult $result */
        foreach ($this->getResults() as $result) {
            $passed = $passed && ($result->isSuccess() || $result->isWarning());
        }

        return $passed;
    }


    protected function addResult(PreRequirementResult $result)
    {
        $this->results[] = $result;
    }
}