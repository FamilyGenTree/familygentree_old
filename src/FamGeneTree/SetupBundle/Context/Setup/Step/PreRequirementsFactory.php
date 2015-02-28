<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Step;

use FamGeneTree\SetupBundle\Context\Setup\Config\ConfigAbstract;
use FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\CheckFilesystem;
use FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\CheckPhpDisabledFunctions;
use FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\CheckPhpIniSettings;
use FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\CheckPhpModules;
use FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\CheckPhpVersion;

/**
 * Class PreRequirementsFactory
 *
 * @package FamGeneTree\SetupBundle\Context\Setup\Step
 * @author  Christoph Graupner <ch.graupner@workingdeveloper.de>
 */
class PreRequirementsFactory extends StepBase
{

    public function getChecks()
    {
        return array(
            new CheckPhpVersion($this->container),
            new CheckPhpModules($this->container),
            new CheckPhpDisabledFunctions($this->container),
            //new CheckDatabase($this->container),
            new CheckPhpIniSettings($this->container),
            new CheckFilesystem($this->container),
        );
    }

    public function check()
    {
        define('WT_REQUIRED_MYSQL_VERSION', '5.0.13');
        define('WT_REQUIRED_PHP_VERSION', '5.3.2');
        define('WT_MODULES_DIR', 'modules_v3/');
        define('WT_GED_ID', null);
        define('WT_PRIV_PUBLIC', 2);
        define('WT_PRIV_USER', 1);
        define('WT_PRIV_NONE', 0);
        define('WT_PRIV_HIDE', -1);
    }

    public function checkConfig(ConfigAbstract $config)
    {
        // TODO: Implement checkConfig() method.
    }
}