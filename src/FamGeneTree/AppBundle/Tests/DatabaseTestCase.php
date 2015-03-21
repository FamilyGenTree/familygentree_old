<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\AppBundle\Tests;

/**
 * Class DatabaseTestCase
 *
 * @package FamGeneTree\AppBundle\Tests
 * @author  Christoph Graupner <ch.graupner@workingdeveloper.net>
 */
abstract class DatabaseTestCase extends AppTestCase
{
    use DbUnitDatabaseTestCaseTrait;

    /**
     * @var \PDO
     */
    protected static $sharedDatabase;

    protected static function bootKernel(array $options = array())
    {
        parent::bootKernel($options);
        if (static::$sharedDatabase === null) {
            $db                     = static::$kernel->getContainer()
                                                     ->get('doctrine.dbal.default_connection');
            static::$sharedDatabase = $db->getWrappedConnection();
        }
        static::$kernel->getContainer()->set('doctrine.dbal.default.default_connection', static::$sharedDatabase);
    }


    /**
     * Returns the test database connection.
     *
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     * @throws \Exception
     */
    protected function getConnection()
    {
        if (static::$sharedDatabase === null) {
            $this->getKernel();
        }

        return new \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection(
            static::$sharedDatabase
        );
    }

    protected function getFixturePath($fixture)
    {
        if (strpos($fixture, 'data/') === 0) {
        }

        return __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . $fixture;
    }

    protected function getDatabaseSettings()
    {

        $container = $this->getContainer();

        return array(
            'dbname'   => $container->getParameter('database_name'),
            'user'     => $container->getParameter('database_user'),
            'password' => $container->getParameter('database_password'),
            'host'     => $container->getParameter('database_host'),
            'port'     => $container->getParameter('database_port')
        );
    }
}