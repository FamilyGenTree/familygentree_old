<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\AppBundle\Tests;


use PHPUnit_Extensions_Database_DB_IDatabaseConnection;

abstract class DatabaseSqliteTestCase extends DatabaseTestCase{
    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        new \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection(
            \DBUnitTestUtility::getSQLiteMemoryDB()
        );
    }
}