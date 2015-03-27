<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck;

use FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\ValueObject\PreRequirementResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckFilesystem extends CheckAbstract
{

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container, 'File System', 'Description');
    }

    /**
     * @return null
     */
    public function run()
    {

        //@file_put_contents(Config::get(Config::DATA_DIRECTORY) . 'test.txt', 'FAB!');
        //$FAB = @file_get_contents(Config::get(Config::DATA_DIRECTORY) . 'test.txt');
        //@unlink(Config::get(Config::DATA_DIRECTORY) . 'test.txt');
        //
        //if ($FAB != 'FAB!') {

        foreach ($this->getPathsToCheck() as $pathToWrite => $accessRules) {
            $state   = PreRequirementResult::STATE_FAILED;
            $message = $accessRules['error-message'];
            $access  = 'Read only';
            switch ($accessRules['access']) {
                case 'rw?c':
                    $access = 'Read + Maybe Write or Create';
                    if (is_writable($pathToWrite)) {
                        $state   = PreRequirementResult::STATE_SUCCESS;
                        $message = null;
                    } elseif (is_writeable(dirname($pathToWrite))) {
                        $state   = PreRequirementResult::STATE_SUCCESS;
                        $message = 'File can be created.';
                    } elseif (is_readable($pathToWrite)) {
                        $state   = PreRequirementResult::STATE_WARNING;
                        $message = $accessRules['warning-message'];
                    }

                    break;
                case 'rw?':
                    $access = 'Read + Maybe Write';
                    if (is_writable($pathToWrite)) {
                        $state   = PreRequirementResult::STATE_SUCCESS;
                        $message = null;
                    } elseif (is_readable($pathToWrite)) {
                        $state   = PreRequirementResult::STATE_WARNING;
                        $message = $accessRules['warning-message'];
                    }
                    break;
                case 'w':
                case 'rw':
                    $access = 'Read+Write';
                    if (is_writeable($pathToWrite)) {
                        $state   = PreRequirementResult::STATE_SUCCESS;
                        $message = null;
                    }
                    break;
                case 'r':
                default:
                    $access = 'Read only';
                    if (is_readable($pathToWrite)) {
                        $state   = PreRequirementResult::STATE_SUCCESS;
                        $message = null;
                    }
                    break;
            }
            $this->addResult(
                new PreRequirementResult(
                    "{$pathToWrite} ({$access})",
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
            $this->container->get('kernel')->getCacheDir()           => array(
                'access'        => 'rw',
                'sub-dirs'      => 'write',
                'error-message' => 'Cache directory and its children must be writable by webserver user ',
            ),
            $rootDir . DIRECTORY_SEPARATOR . 'config/parameters.yml' => array(
                'access'          => 'rw?c',
                'sub-dirs'        => 'write',
                'error-message'   => 'This file or the containing directory should be writable, if this setup wizard should write the settings for you.',
                'warning-message' => 'Warning'
            )
        );
    }
}