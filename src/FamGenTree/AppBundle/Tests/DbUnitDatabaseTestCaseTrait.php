<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGenTree\AppBundle\Tests;

use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Trait DbUnitDatabaseTestCaseTrait
 *
 * All methods from \PHPUnit_Extensions_Database_TestCase as trait
 *
 * @package FamGenTree\AppBundle\Tests
 * @author  Christoph Graupner <ch.graupner@workingdeveloper.net>
 */
trait DbUnitDatabaseTestCaseTrait
{
    /**
     * @var \PHPUnit_Extensions_Database_ITester
     */
    protected $databaseTester;

    /**
     * Asserts that two given tables are equal.
     *
     * @param \PHPUnit_Extensions_Database_DataSet_ITable $expected
     * @param \PHPUnit_Extensions_Database_DataSet_ITable $actual
     * @param string                                      $message
     */
    public static function assertTablesEqual(\PHPUnit_Extensions_Database_DataSet_ITable $expected, \PHPUnit_Extensions_Database_DataSet_ITable $actual, $message = '')
    {
        $constraint = new \PHPUnit_Extensions_Database_Constraint_TableIsEqual($expected);

        self::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that two given datasets are equal.
     *
     * @param \PHPUnit_Extensions_Database_DataSet_IDataSet $expected
     * @param \PHPUnit_Extensions_Database_DataSet_IDataSet $actual
     * @param string                                        $message
     */
    public static function assertDataSetsEqual(\PHPUnit_Extensions_Database_DataSet_IDataSet $expected, \PHPUnit_Extensions_Database_DataSet_IDataSet $actual, $message = '')
    {
        $constraint = new \PHPUnit_Extensions_Database_Constraint_DataSetIsEqual($expected);

        self::assertThat($actual, $constraint, $message);
    }

    /**
     * Assert that a given table has a given amount of rows
     *
     * @param string $tableName Name of the table
     * @param int    $expected  Expected amount of rows in the table
     * @param string $message   Optional message
     */
    public function assertTableRowCount($tableName, $expected, $message = '')
    {
        $constraint = new \PHPUnit_Extensions_Database_Constraint_TableRowCount($tableName, $expected);
        $actual     = $this->getConnection()->getRowCount($tableName);

        self::assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that a given table contains a given row
     *
     * @param array                                       $expectedRow Row expected to find
     * @param \PHPUnit_Extensions_Database_DataSet_ITable $table       Table to look into
     * @param string                                      $message     Optional message
     */
    public function assertTableContains(array $expectedRow, \PHPUnit_Extensions_Database_DataSet_ITable $table, $message = '')
    {
        self::assertThat($table->assertContainsRow($expectedRow), self::isTrue(), $message);
    }

    /**
     * Closes the specified connection.
     *
     * @param \PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection
     */
    protected function closeConnection(\PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection)
    {
        $this->getDatabaseTester()->closeConnection($connection);
    }

    /**
     * Returns the test database connection.
     *
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected abstract function getConnection();

    /**
     * @return KernelInterface
     */
    protected abstract function getKernel();

    /**
     * Gets the IDatabaseTester for this testCase. If the IDatabaseTester is
     * not set yet, this method calls newDatabaseTester() to obtain a new
     * instance.
     *
     * @return \PHPUnit_Extensions_Database_ITester
     */
    protected function getDatabaseTester()
    {
        if (empty($this->databaseTester)) {
            $this->databaseTester = $this->newDatabaseTester();
        }

        return $this->databaseTester;
    }

    /**
     * Returns the test dataset.
     *
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected abstract function getDataSet();

    /**
     * Returns the database operation executed in test setup.
     *
     * @return \PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    protected function getSetUpOperation()
    {
        return \PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT();
    }

    /**
     * Returns the database operation executed in test cleanup.
     *
     * @return \PHPUnit_Extensions_Database_Operation_IDatabaseOperation
     */
    protected function getTearDownOperation()
    {
        return \PHPUnit_Extensions_Database_Operation_Factory::NONE();
    }

    /**
     * Creates a IDatabaseTester for this testCase.
     *
     * @return \PHPUnit_Extensions_Database_ITester
     */
    protected function newDatabaseTester()
    {
        return new \PHPUnit_Extensions_Database_DefaultTester($this->getConnection());
    }

    /**
     * Creates a new DefaultDatabaseConnection using the given PDO connection
     * and database schema name.
     *
     * @param \PDO   $connection
     * @param string $schema
     *
     * @return \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    protected function createDefaultDBConnection(\PDO $connection, $schema = '')
    {
        return new \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection($connection, $schema);
    }

    /**
     * Creates a new FlatXmlDataSet with the given $xmlFile. (absolute path.)
     *
     * @param string $xmlFile
     *
     * @return \PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet
     */
    protected function createFlatXMLDataSet($xmlFile)
    {
        return new \PHPUnit_Extensions_Database_DataSet_FlatXmlDataSet($xmlFile);
    }

    /**
     * Creates a new XMLDataSet with the given $xmlFile. (absolute path.)
     *
     * @param string $xmlFile
     *
     * @return \PHPUnit_Extensions_Database_DataSet_XmlDataSet
     */
    protected function createXMLDataSet($xmlFile)
    {
        return new \PHPUnit_Extensions_Database_DataSet_XmlDataSet($xmlFile);
    }

    /**
     * Create a a new MysqlXmlDataSet with the given $xmlFile. (absolute path.)
     *
     * @param string $xmlFile
     *
     * @return \PHPUnit_Extensions_Database_DataSet_MysqlXmlDataSet
     * @since  Method available since Release 1.0.0
     */
    protected function createMySQLXMLDataSet($xmlFile)
    {
        return new \PHPUnit_Extensions_Database_DataSet_MysqlXmlDataSet($xmlFile);
    }

    /**
     * Returns an operation factory instance that can be used to instantiate
     * new operations.
     *
     * @return \PHPUnit_Extensions_Database_Operation_Factory
     */
    protected function getOperations()
    {
        return new \PHPUnit_Extensions_Database_Operation_Factory();
    }

    /**
     * Performs operation returned by getSetUpOperation().
     */
    protected function setUp()
    {
        parent::setUp();

        $this->databaseTester = null;

        $this->getDatabaseTester()->setSetUpOperation($this->getSetUpOperation());
        $this->getDatabaseTester()->setDataSet($this->getDataSet());
        $this->getDatabaseTester()->onSetUp();
    }

    /**
     * Performs operation returned by getSetUpOperation().
     */
    protected function tearDown()
    {
        $this->getDatabaseTester()->setTearDownOperation($this->getTearDownOperation());
        $this->getDatabaseTester()->setDataSet($this->getDataSet());
        $this->getDatabaseTester()->onTearDown();

        /**
         * Destroy the tester after the test is run to keep DB connections
         * from piling up.
         */
        $this->databaseTester = null;
    }

    /**
     * @param string      $fixtureSqlPath
     * @param string|null $prefix
     */
    protected function importDump($fixtureSqlPath, $prefix = null)
    {
        $pdo = $this->getConnection()->getConnection();
        $sql = file_get_contents($fixtureSqlPath);
        if (null !== $prefix) {
            $sql = str_replace('###PREFIX###', $prefix, $sql);
        }
        $pdo->exec('SET foreign_key_checks = 0;');
        $pdo->exec($sql);
        $pdo->exec('SET foreign_key_checks = 1;');
    }

    protected function emptyDatabase()
    {
        $pdo = $this->getConnection()->getConnection();
        $pdo->exec('SET foreign_key_checks = 0;');
        $statement = $pdo->query('SHOW TABLES;');
        $rows      = $statement->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($rows as $row) {
            $pdo->exec("DROP TABLE IF EXISTS {$row}");
        }
        $pdo->exec('SET foreign_key_checks = 1;');
    }

    protected function exec($sql, $prefix = null)
    {
        $pdo = $this->getConnection()->getConnection();
        if (null !== $prefix) {
            $sql = str_replace('###PREFIX###', $prefix, $sql);
        }
        $pdo->exec($sql);
    }
}