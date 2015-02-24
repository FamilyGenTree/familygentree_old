<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck;


use FamGeneTree\SetupBundle\Context\Setup\ValueObject\PreRequirementResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckPhpModules extends CheckAbstract
{

    protected static $REQUIRED_MODULES = array(
        'intl',
        'mysql'
    );

    protected static $RECOMMENDED_MODULES = array();

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container,'php-modules', 'Description');
    }

    /**
     * @return null
     */
    public function run()
    {
        foreach (static::$REQUIRED_MODULES as $moduleName) {
            $resultName = "PHP Module {$moduleName}";
            $result     = null;
            if (false === extension_loaded($moduleName)) {
                $result = new PreRequirementResult(
                    $resultName,
                    PreRequirementResult::STATE_FAILED,
                    'Missing'
                );
            } else {
                $result = new PreRequirementResult(
                    $resultName,
                    PreRequirementResult::STATE_SUCCESS
                );
            }
            $this->addResult($result);
        }

        foreach (static::$RECOMMENDED_MODULES as $moduleName) {
            $resultName = "PHP Module {$moduleName}";
            $result     = null;
            if (false === extension_loaded($moduleName)) {
                $result = new PreRequirementResult(
                    $resultName,
                    PreRequirementResult::STATE_WARNING,
                    'Should be installed'
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