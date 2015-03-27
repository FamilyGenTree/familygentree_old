<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck;


use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckDatabase extends CheckAbstract
{
    function __construct(ContainerInterface $container)
    {
        parent::__construct($container,'database', 'Description');
    }

    /**
     * @return null
     */
    public function run()
    {
        // TODO: Implement run() method.
    }
}