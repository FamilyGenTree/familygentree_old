<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck;


use FamGeneTree\SetupBundle\Context\Setup\ValueObject\PreRequirementResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckPhpVersion extends CheckAbstract implements CheckInterface
{
    const REQUIRED_VERSION = '5.4.0';

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container, 'php-version', 'Description');
    }

    public function run()
    {
        if (version_compare(PHP_VERSION, static::REQUIRED_VERSION) < 0) {
            $this->addResult(
                new PreRequirementResult(
                    'PHP Version',
                    PreRequirementResult::STATE_FAILED,
                    'Sorry, the setup wizard cannot start.'
                    . 'This server is running PHP version ' . PHP_VERSION
                    . 'PHP ', static::REQUIRED_VERSION, ' (or any later version) is required'
                )
            );
        } else {
            $this->addResult(
                new PreRequirementResult(
                    'PHP Version',
                    PreRequirementResult::STATE_SUCCESS,
                    'Found PHP version ' . PHP_VERSION
                    . ' required was ' . static::REQUIRED_VERSION
                )
            );
        }
    }
}