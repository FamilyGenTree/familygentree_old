<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Config;

use FamGeneTree\SetupBundle\Context\Setup\SetupManager;

class SetupConfig
{
    const STEP_PRE_REQUIREMENTS     = 1;
    const STEP_LOCALE               = 0;
    const STEP_DATABASE_CREDENTIALS = 2;


    const STEPSTATE_COMPLETED   = 2;
    const STEPSTATE_STARTED     = 1;
    const STEPSTATE_NOT_STARTED = 0;

    const STEP_START  = self::STEP_LOCALE;
    const STEP_FINISH = 10;

    protected $setupLocale    = null;
    protected $completedSteps = array();
    protected $currentStep    = self::STEP_START;
    /** @var ConfigDatabase */
    protected $configDatabase = null;

    /**
     * @param $locale
     */
    public function setSetupLocale($locale)
    {
        $this->setupLocale = $locale;
    }

    /**
     * @return string
     */
    public function getSetupLocale()
    {
        return \Locale::getDefault();
    }

    public function getParametersYamlContent()
    {
    }

    public function getFirstUserValues()
    {
    }

    public function setStepCompleted($stepId)
    {
        $this->completedSteps[$stepId] = static::STEPSTATE_COMPLETED;
    }

    public function isStepCompleted($stepId)
    {
        return isset($this->completedSteps[$stepId]) && $this->completedSteps[$stepId] == true;
    }

    public function setCurrentStep($stepId)
    {
        $this->currentStep = $stepId;
    }

    /**
     * @return int
     */
    public function getCurrentStep()
    {
        return $this->currentStep;
    }

    /**
     * @return \FamGeneTree\SetupBundle\Context\Setup\Config\ConfigDatabase
     */
    public function getConfigDatabase()
    {
        if (null === $this->configDatabase) {
            $this->configDatabase = new ConfigDatabase();
        }

        return $this->configDatabase;
    }
}