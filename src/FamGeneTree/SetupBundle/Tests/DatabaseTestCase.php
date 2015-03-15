<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\SetupBundle\Tests;

use FamGeneTree\AppBundle\Tests\DatabaseTestCase as BaseDatabaseTestCase;

abstract class DatabaseTestCase extends BaseDatabaseTestCase
{
    protected function getFixturePath($fixture)
    {
        if (strpos($fixture, 'data/') === 0) {
        }

        return __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . $fixture;
    }
}