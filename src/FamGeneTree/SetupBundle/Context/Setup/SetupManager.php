<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup;

use FamGeneTree\SetupBundle\Context\Setup\Config\SetupConfig;
use FamGeneTree\SetupBundle\Context\Setup\Exception\SetUpException;
use FamGeneTree\SetupBundle\Context\Setup\Step\FirstSettingsStep;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SetupManager
 *
 * @package FamGeneTree\SetupBundle\Context\Setup
 * @author  Christoph Graupner <ch.graupner@workingdeveloper.net>
 */
class SetupManager
{
    protected static $MAIN_STEP_ORDER = array(
        SetupConfig::STEP_LOCALE                  => 'fgt.setup.step.locale',
        SetupConfig::STEP_PRE_REQUIREMENTS        => 'fgt.setup.step.pre_requirements',
        SetupConfig::STEP_DATABASE_CREDENTIALS    => 'fgt.setup.step.database',
        SetupConfig::STEP_DATABASE_RUN_MIGRATIONS => 'fgt.setup.step.database.create',
        SetupConfig::STEP_FIRST_SETTINGS              => 'fgt.setup.step.first_user',
        SetupConfig::STEP_FINISH                  => 'fgt.setup.step.finish'
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

    public function getConfigFirstSettings()
    {
        return $this->getSetupConfig()->getConfigFirstUser();
    }

    /**
     * @return \FamGeneTree\SetupBundle\Context\Setup\Step\DatabaseSettingsStep
     */
    public function getStepServiceDatabase()
    {
        return $this->container->get('fgt.setup.service.step.database');
    }

    /**
     * @return FirstSettingsStep
     */
    public function getStepServiceFirstSettings()
    {
        return $this->container->get('fgt.setup.service.step.first_settings');
    }

    public function getStepAfterMigration()
    {
        return SetupConfig::STEP_FINISH;
    }

    /**
     *
     * @param bool $createIfAbsent
     *
     * @return \FamGeneTree\SetupBundle\Context\Setup\Config\SetupConfig
     */
    public function getSetupConfig($createIfAbsent = true)
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