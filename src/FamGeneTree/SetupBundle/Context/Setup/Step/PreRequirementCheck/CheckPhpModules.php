<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementCheck;

use FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\ValueObject\PreRequirementResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckPhpModules extends CheckAbstract
{

    protected static $REQUIRED_MODULES = array(
        'intl'      => 'Missing, please install',
        'pcre'      => 'Missing, please install',
        'pdo'       => 'Missing, please install',
        'pdo_mysql' => 'Missing, please install',
        'session'   => 'Missing, please install',
        'iconv'     => 'Missing, please install'
    );

    protected static $RECOMMENDED_MODULES = array(
        'gd'        => 'Recommended for creating thumbnails of images',
        'xml'       => 'Recommended for reporting',
        'simplexml' => 'Recommended for reporting',

    );

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container, 'Needed PHP Modules', 'Description');
    }

    /**
     * @return null
     */
    public function run()
    {
        foreach (static::$REQUIRED_MODULES as $moduleName => $message) {
            $resultName = "PHP Module '{$moduleName}'";
            $result     = null;
            if (false === extension_loaded($moduleName)) {
                $result = new PreRequirementResult(
                    $resultName,
                    PreRequirementResult::STATE_FAILED,
                    $message
                );
            } else {
                $result = new PreRequirementResult(
                    $resultName,
                    PreRequirementResult::STATE_SUCCESS
                );
            }
            $this->addResult($result);
        }

        foreach (static::$RECOMMENDED_MODULES as $moduleName => $message) {
            $resultName = "PHP Module '{$moduleName}'";
            $result     = null;
            if (false === extension_loaded($moduleName)) {
                $result = new PreRequirementResult(
                    $resultName,
                    PreRequirementResult::STATE_WARNING,
                    $message
                );
            } else {
                $result = new PreRequirementResult(
                    $resultName,
                    PreRequirementResult::STATE_SUCCESS
                );
            }
            $this->addResult($result);
        }
    }
}