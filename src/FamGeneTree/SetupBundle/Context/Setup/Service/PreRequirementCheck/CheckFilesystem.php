<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck;

use FamGeneTree\SetupBundle\Context\Setup\ValueObject\PreRequirementResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckFilesystem extends CheckAbstract
{

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container, 'filesystem', 'Description');
    }

    /**
     * @return null
     */
    public function run()
    {
        foreach ($this->getPathsToCheck() as $pathToWrite => $accessRules) {
            $state   = PreRequirementResult::STATE_FAILED;
            $message = $accessRules['error-message'];
            switch ($accessRules['write']) {
                case 'maybe':
                    if (is_writable($pathToWrite)) {
                        $state   = PreRequirementResult::STATE_SUCCESS;
                        $message = null;
                    } elseif (is_readable($pathToWrite)) {
                        $state   = PreRequirementResult::STATE_WARNING;
                        $message = $accessRules['warning-message'];
                    }
                    break;
                case true:
                    if (is_writeable($pathToWrite)) {
                        $state   = PreRequirementResult::STATE_SUCCESS;
                        $message = null;
                    }
                    break;
            }
            $this->addResult(
                new PreRequirementResult(
                    $pathToWrite,
                    $state,
                    $message
                )
            );
        }
    }

    protected function getPathsToCheck()
    {

        $rootDir = $this->container->get('kernel')->getRootDir();

        return array(
            $this->container->get('kernel')->getCacheDir()          => array(
                'write'         => true,
                'sub-dirs'      => 'write',
                'error-message' => 'Cache directory and its children must be writable by webserver user ',
            ),
            $rootDir . DIRECTORY_SEPARATOR . 'config/parameters.yml' => array(
                'write'           => 'maybe',
                'sub-dirs'        => 'write',
                'error-message'   => 'For setting ',
                'warning-message' => 'Warning'
            )
        );
    }
}