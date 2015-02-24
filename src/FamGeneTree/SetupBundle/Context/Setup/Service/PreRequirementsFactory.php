<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Service;

use FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck\CheckDatabase;
use FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck\CheckFilesystem;
use FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck\CheckPhpModules;
use FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck\CheckPhpVersion;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PreRequirementsFactory
 *
 * @package FamGeneTree\SetupBundle\Context\Setup\Service
 * @author  Christoph Graupner <ch.graupner@workingdeveloper.de>
 */
class PreRequirementsFactory extends ContainerAware
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getChecks()
    {
        return array(
            new CheckPhpVersion($this->container),
            new CheckPhpModules($this->container),
            new CheckDatabase($this->container),
            new CheckFilesystem($this->container)
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
}