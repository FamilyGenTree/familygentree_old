<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Config;


class SetupConfig
{
    const STEP_PRE_REQUIREMENTS     = 1;
    const STEP_LOCALE          = 0;
    const STEP_DATABASE_CREDENTIALS = 2;

    const STEPSTATE_COMPLETED   = 2;
    const STEPSTATE_STARTED     = 1;
    const STEPSTATE_NOT_STARTED = 0;
    protected $step = null;

    protected $setupLocale         = null;
    protected $completedSteps = array();

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

}