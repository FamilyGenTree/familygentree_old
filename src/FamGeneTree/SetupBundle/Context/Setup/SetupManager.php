<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup;

use FamGeneTree\SetupBundle\Context\Setup\Config\SetupConfig;
use FamGeneTree\SetupBundle\Context\Setup\Step\DatabaseSetup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SetupManager
{
    protected static $MAIN_STEP_ORDER = array(
        SetupConfig::STEP_LOCALE               => 'fgt.setup.step.locale',
        SetupConfig::STEP_PRE_REQUIREMENTS     => 'fgt.setup.step.pre_requirements',
        SetupConfig::STEP_DATABASE_CREDENTIALS => 'fgt.setup.step.database',
        SetupConfig::STEP_FINISH               => 'fgt.setup.step.finish'
    );

    protected $stepOrder;
    protected $stepRouteMap;

    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->stepRouteMap = static::$MAIN_STEP_ORDER;
    }

    public function setCurrentStep($stepId)
    {
        $this->getSetupConfig(true)->setCurrentStep($stepId);
    }

    public function getCurrentStep()
    {
        return $this->getSetupConfig()->getCurrentStep();
    }

    public function getFirstIncompleteStep()
    {
        foreach ($this->getStepOrder() as $step) {
            if (false === $this->getSetupConfig(true)->isStepCompleted($step)) {
                return $step;
            }
        }

        return SetupConfig::STEP_LOCALE;
    }

    public function getRouteToStep($step)
    {
        return isset($this->stepRouteMap[$step]) ? $this->stepRouteMap[$step] : null;
    }

    public function setCurrentStepCompleted()
    {
        $stepId = $this->getCurrentStep();
        $this->getSetupConfig()->setStepCompleted($stepId);

        return $this;
    }

    public function setStepCompleted($stepId)
    {
        $this->getSetupConfig()->setStepCompleted($stepId);

        return $this;
    }

    /**
     * @param int $currentStep
     *
     * @return int|null
     */
    public function getNextStep($currentStep = null)
    {
        if ($currentStep === null) {
            $currentStep = $this->getCurrentStep();
        }
        $stepOrder = $this->getStepOrder();
        foreach ($stepOrder as $idx => $step) {
            if ($step === $currentStep) {
                if (isset($stepOrder[$idx + 1])) {
                    return $stepOrder[$idx + 1];
                } else {
                    return null;
                }
            }
        }

        return null;
    }

    public function getPreviousStep($currentStep = null)
    {
        if ($currentStep === null) {
            $currentStep = $this->getCurrentStep();
        }
        $stepOrder = $this->getStepOrder();
        foreach ($stepOrder as $idx => $step) {
            if ($step === $currentStep) {
                if (isset($stepOrder[$idx - 1])) {
                    return $stepOrder[$idx - 1];
                } else {
                    return null;
                }
            }
        }

        return null;
    }

    /**
     * @return \FamGeneTree\SetupBundle\Context\Setup\Config\ConfigDatabase
     */
    public function getConfigDatabase()
    {
        return $this->getSetupConfig()->getConfigDatabase();
    }

    /**
     * @return \FamGeneTree\SetupBundle\Context\Setup\Step\DatabaseSetup
     */
    public function getStepDatabase()
    {
        return $this->container->get('fgt.setup.service.step.database');
    }

    /**
     *
     * @param bool $createIfAbsent
     *
     * @return \FamGeneTree\SetupBundle\Context\Setup\Config\SetupConfig
     */
    protected function getSetupConfig($createIfAbsent = true)
    {
        $request     = $this->container->get('request');
        $setupConfig = $request->getSession()->get('setup-config');
        if ($createIfAbsent && null === $setupConfig) {
            $setupConfig = new SetupConfig();
            $request->getSession()->set('setup-config', $setupConfig);
        }

        return $setupConfig;
    }

    protected function getStepOrder()
    {
        if ($this->stepOrder === null) {
            $this->stepOrder = array_keys($this->stepRouteMap);
        }

        return $this->stepOrder;
    }
}