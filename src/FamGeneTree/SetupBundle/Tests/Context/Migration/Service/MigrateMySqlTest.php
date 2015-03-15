<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\SetupBundle\Tests\Context\Migration\Service;

use FamGeneTree\SetupBundle\Context\Migration\Plan\MigrationPlan;
use FamGeneTree\SetupBundle\Context\Migration\Plan\Step\MigrationPlanSqlStep;
use FamGeneTree\SetupBundle\Context\Migration\Service\Migrate;
use FamGeneTree\SetupBundle\Context\Setup\Config\ConfigDatabase;
use FamGeneTree\SetupBundle\Tests\Context\Migration\Service\Step\Migration\Migration20150401_0000b_Test;
use FamGeneTree\SetupBundle\Tests\Context\Migration\Service\Step\WtMigration\MigrateWt1_6_2to20150401_0000b_Test;
use FamGeneTree\SetupBundle\Tests\DatabaseTestCase;

/**
 * Class MigrateMySqlTest
 *
 * @package FamGeneTree\SetupBundle\Tests\Context\Migration\Service
 * @author  Christoph Graupner <ch.graupner@workingdeveloper.net>
 */
class MigrateMySqlTest extends DatabaseTestCase
{
    const FIXTURE_MIGRATION    = 'migrate/migrations';
    const FIXTURE_WT_MIGRATION = 'migrate/wt_migrations';

    const TABLE_PREFIX = 'tm_';

    /**
     * @dataProvider dataProviderTestIsMigrationNeeded
     *
     * @param string|null    $fixtureDbDumpPath null == empty DB
     * @param ConfigDatabase $fixtureConfig
     * @param bool           $expectedIsNeeded
     * @param MigrationPlan  $expectedPlan
     */
    public function testIsGetMigrationNeeded(
        $fixtureDbDumpPath,
        ConfigDatabase $fixtureConfig,
        $expectedIsNeeded,
        $expectedPlan
    ) {
        if ($this->checkSkipped($fixtureDbDumpPath)) {
            return;
        }

        // fixture set up
        if (null !== $fixtureDbDumpPath) {
            $this->importDump($fixtureDbDumpPath, static::TABLE_PREFIX);
        }

        /** @var Migrate $sut */
        $sut = $this->getMigrationMock($fixtureConfig);

        $sut->setDatabaseConfig(
            $fixtureConfig
        );
        //test isMigrationNeeded
        $this->assertEquals($expectedIsNeeded, $sut->isMigrationNeeded());

        //test getMigrationPlan
        if ($expectedIsNeeded === true) {
            $this->assertPlanEquals($expectedPlan, $sut->getMigrationPlan());
        }
        //test runMigration
    }

    /**
     * The current latest tag for the database to migration from is set in the fixture-sql-file ($fixtureDb)
     *
     * @return array
     */
    public function dataProviderTestIsMigrationNeeded()
    {
        $testcases    = array();
        $testdbConfig = $this->getDatabaseSettings();
        $config       = new ConfigDatabase();
        $config->setDbname($testdbConfig['dbname']);
        $config->setUser($testdbConfig['user']);
        $config->setHost($testdbConfig['host']);
        $config->setPassword($testdbConfig['password']);
        $config->setPrefix(static::TABLE_PREFIX);

        $testcases['latest db'] = array(
            '$fixtureDb'             => $this->getFixturePath('migrate/mysql/latest-db-version.sql'),
            '$fixDbConfig'           => $config,
            '$expectedIsNeeded'      => false,
            '$expectedMigrationPlan' => null
        );

        require_once $this->getFixturePath(static::FIXTURE_MIGRATION . '/20150401-0000b_basis.php');
        require_once $this->getFixturePath(static::FIXTURE_WT_MIGRATION . '/Wt1_6_2-to-20150401-0000b.php');

        $step1 = new MigrationPlanSqlStep(
            $this->getConnection()
                 ->getConnection(),
            static::TABLE_PREFIX,
            '00000000-0000',
            '00000000-0000_basis.sql'
        );
        $step2 = new MigrationPlanSqlStep(
            $this->getConnection()
                 ->getConnection(),
            static::TABLE_PREFIX,
            '20150401-0000a',
            '20150401-0000a_basis.sql'
        );

        $step3 = new Migration20150401_0000b_Test (
            $this->getConnection()
                 ->getConnection(),
            static::TABLE_PREFIX,
            '20150401-0000b'
        );
        $step4 = new MigrationPlanSqlStep(
            $this->getConnection()
                 ->getConnection(),
            static::TABLE_PREFIX,
            '20151231-1437',
            '20151231-1437_testend.sql'
        );

        $stepWt162 = new MigrateWt1_6_2to20150401_0000b_Test(
            $this->getConnection()
                 ->getConnection(),
            static::TABLE_PREFIX,
            '00000162-0000'
        // 'Wt1_6_2-to-20150401-0000b.php'
        );

        $migrationPlan = new MigrationPlan(
            Migrate::DB_SYSTEM_MYSQL,
            $this->getConnection()
                 ->getConnection()
        );
        $migrationPlan->addMigration($step1);
        $migrationPlan->addMigration($step2);
        $migrationPlan->addMigration($step3);
        $migrationPlan->addMigration($step4);
        $testcases['empty db'] = array(
            '$fixtureDb'             => null,
            '$fixDbConfig'           => $config,
            '$expectedIsNeeded'      => true,
            '$expectedMigrationPlan' => $migrationPlan
        );

        $migrationPlan = new MigrationPlan(
            Migrate::DB_SYSTEM_MYSQL,
            $this->getConnection()
                 ->getConnection()
        );
        $migrationPlan->addMigration($step3);
        $migrationPlan->addMigration($step4);
        $testcases['older version'] = array(
            '$fixtureDb'             => $this->getFixturePath('migrate/mysql/older-db-version.sql'),
            '$fixDbConfig'           => $config,
            '$expectedIsNeeded'      => true,
            '$expectedMigrationPlan' => $migrationPlan
        );

        $migrationPlan = new MigrationPlan(
            Migrate::DB_SYSTEM_MYSQL,
            $this->getConnection()
                 ->getConnection()
        );
        $migrationPlan->addMigration($stepWt162);
        $migrationPlan->addMigration($step4);
        $testcases['WT 1.6.2'] = array(
            '$fixtureDb'             => static::SKIP_TEST_MARKER . ':implementation WT 1.6.2 migration!',
            //'$fixtureDb'             => $this->getFixturePath('migrate/mysql/wt1.6.2.sql'),
            '$fixDbConfig'           => $config,
            '$expectedIsNeeded'      => true,
            '$expectedMigrationPlan' => $migrationPlan
        );

        return $testcases;
    }

    /**
     * @dataProvider dataProviderTestIsWebtreesDatabase
     *
     * @param string         $fixtureDbDumpPath
     * @param ConfigDatabase $fixtureConfig
     * @param bool           $expectedIsNeeded
     *
     */
    public function testIsWebtreesDatabase(
        $fixtureDbDumpPath,
        ConfigDatabase $fixtureConfig,
        $expectedIsNeeded
    ) {
        if ($this->checkSkipped($fixtureDbDumpPath)) {
            return;
        }
        // fixture set up
        if (null !== $fixtureDbDumpPath) {
            $this->importDump($fixtureDbDumpPath, static::TABLE_PREFIX);
        }

        /** @var Migrate $sut */
        $sut  = $this->getMigrationMock($fixtureConfig, '20150401-0000');
        $refl = $this->makeMethodAccessible($sut, 'isWebtreesDatabase');

        $this->assertEquals($expectedIsNeeded, $refl->invoke($sut));
    }

    public function dataProviderTestIsWebtreesDatabase()
    {
        $testcases    = array();
        $testdbConfig = $this->getDatabaseSettings();
        $config       = new ConfigDatabase();
        $config->setDbname($testdbConfig['dbname']);
        $config->setUser($testdbConfig['user']);
        $config->setHost($testdbConfig['host']);
        $config->setPassword($testdbConfig['password']);
        $config->setPrefix(static::TABLE_PREFIX);

        $testcases['latest db'] = array(
            '$fixtureDb'   => $this->getFixturePath('migrate/mysql/latest-db-version.sql'),
            '$fixDbConfig' => $config,
            '$expectedIs'  => false,
        );

        $testcases['empty db'] = array(
            '$fixtureDb'   => null,
            '$fixDbConfig' => $config,
            '$expectedIs'  => false,
        );

        $testcases['WT 1.6.2'] = array(
            '$fixtureDb'   => $this->getFixturePath('migrate/mysql/wt1.6.2.sql'),
            '$fixDbConfig' => $config,
            '$expectedIs'  => true,
        );

        return $testcases;
    }

    /**
     * @dataProvider dataProviderTestIsMigrationNeeded
     */
    public function testGetMigrationPlan()
    {
    }

    public function testRunMigration()
    {
    }


    public function testGetAvailableMigrations()
    {
    }

    protected function tearDown()
    {
        $this->emptyDatabase();
        parent::tearDown();
    }

    /**
     * Returns the test dataset.
     *
     * @return \PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }

    /**
     * @param ConfigDatabase $fixtureConfig
     *
     * @return \FamGeneTree\SetupBundle\Context\Migration\Service\Migrate
     */
    protected function getMigrationMock(ConfigDatabase $fixtureConfig)
    {
        /** @var Migrate $mock */
        $mock = $this->getMockBuilder('FamGeneTree\SetupBundle\Context\Migration\Service\Migrate')
                     ->disableOriginalConstructor()
                     ->setConstructorArgs(array($this->getContainer()))
                     ->setMethods(array(
                                      'getMigrationsPath'
                                  ))
                     ->getMock();
        $mock->setDatabaseConfig(
            $fixtureConfig
        );

        $mock->method('getMigrationsPath')
             ->willReturn($this->getFixturePath(static::FIXTURE_MIGRATION));

        return $mock;
    }

    /**
     * @param MigrationPlan|null $expectedPlan
     * @param MigrationPlan|null $actual
     *
     * @throws \Exception
     */
    private function assertPlanEquals($expectedPlan, $actual)
    {
        if (null === $expectedPlan) {
            $this->assertEquals($expectedPlan, $actual);
        } else {
            $this->assertInstanceOf('\FamGeneTree\SetupBundle\Context\Migration\Plan\MigrationPlan', $actual);
            $actualSteps = $actual->getSteps();

            $expectedSteps = $expectedPlan->getSteps();
            $this->assertEquals(count($expectedSteps), count($actualSteps), 'count of migration steps differ');
            foreach ($expectedSteps as $step) {
                $this->assertEquals($step->getPatchId(), array_shift($actualSteps)->getPatchId());
            }
        }
    }
}
