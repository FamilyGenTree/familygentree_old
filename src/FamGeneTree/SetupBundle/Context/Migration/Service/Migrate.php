<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Migration\Service;

use FamGeneTree\SetupBundle\Context\Migration\Exception\MigrationException;
use FamGeneTree\SetupBundle\Context\Migration\Exception\MigrationPlanStepClassNotFound;
use FamGeneTree\SetupBundle\Context\Migration\Plan\MigrationPlan;
use FamGeneTree\SetupBundle\Context\Migration\Plan\Step\MigrationPlanSqlStep;
use FamGeneTree\SetupBundle\Context\Setup\Config\ConfigDatabase;
use FamGeneTree\SetupBundle\Context\Setup\Step\StepResultAggregate;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Migrate extends ContainerAware
{
    const TABLE_PREFIX               = '###PREFIX###';
    const DB_SYSTEM_MYSQL            = ConfigDatabase::DB_SYSTEM_MYSQL;
    const MIGRATION_FILENAME_PATTERN = '/^(\d{8,8}-\d{4,4}[a-z]*)[-_](.*)$/';

    /**
     * @var \FamGeneTree\SetupBundle\Context\Setup\Config\ConfigDatabase
     */
    protected $databaseConfig = null;
    protected $pdo            = null;
    /**
     * max(patch_id) in schema_update to
     *
     * @var null
     */
    protected $expectedSchemaVersion = null;

    /**
     * @var MigrationPlan
     */
    protected $migrationPlan = null;
    /**
     * @var array|null
     */
    protected $availableMigrationFiles = null;

    /**
     * @var StepResultAggregate
     */
    protected $results = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \FamGeneTree\SetupBundle\Context\Setup\Config\ConfigDatabase
     */
    public function getDatabaseConfig()
    {
        return $this->databaseConfig;
    }

    /**
     * @param \FamGeneTree\SetupBundle\Context\Setup\Config\ConfigDatabase $databaseConfig
     */
    public function setDatabaseConfig(ConfigDatabase $databaseConfig)
    {
        $this->databaseConfig = $databaseConfig;
    }

    /**
     * @return bool
     */
    public function isMigrationNeeded()
    {
        return $this->getMigrationPlan() !== false;
    }

    /**
     * @return MigrationPlan |bool false if no migration is needed
     */
    public function getMigrationPlan()
    {
        if (null === $this->migrationPlan) {
            try {
                $pdo               = $this->getConnection();
                $sql               = $this->applyPrefixed('SELECT MAX(patch_id) FROM `###PREFIX###schema_updates`;');
                $statement         = $pdo->query($sql);
                $currentPatchLevel = $statement->fetch(\PDO::FETCH_COLUMN);
                if (false === $currentPatchLevel) {
                    if ($this->isWebtreesDatabase()) {
                        $this->migrationPlan = $this->createMigrationPlanForWebtreesDb();
                    } else {
                        $this->migrationPlan = $this->createMigrationPlanForEmptyDb();
                    }
                } elseif ($this->getMaxAvailablePatchTag() > $currentPatchLevel) {
                    $this->migrationPlan = $this->createMigrationPlan($currentPatchLevel);
                } else {
                    $this->migrationPlan = false;
                }
            } catch (\PDOException $ex) {
                if ($this->isWebtreesDatabase()) {
                    $this->migrationPlan = $this->createMigrationPlanForWebtreesDb();
                } else {
                    $this->migrationPlan = $this->createMigrationPlanForEmptyDb();
                }
            }
        }

        return $this->migrationPlan;
    }

    /**
     *
     */
    public function executeMigrations()
    {
        $plan = $this->getMigrationPlan();
        if (false !== $plan && $plan instanceof MigrationPlan) {
            try {
                $plan->execute();
                $this->results = $plan->getResults();
            } catch (\Exception $ex) {
                $this->results = $plan->getResults();
                throw $ex;
            }
        } else {
            $this->results = new StepResultAggregate(
                'Migration'
            );

        }

    }

    /**
     * @return StepResultAggregate
     */
    public function getResults()
    {
        return $this->results;
    }

    protected function getConnection()
    {
        if (null === $this->pdo) {
            $config    = $this->getDatabaseConfig();
            $dsn       = "{$config->getDbSystem()}:host={$config->getHost()};dbname={$config->getDbname()}";
            $this->pdo = new \PDO(
                $dsn,
                $config->getUser(),
                $config->getPassword(),
                array(
                    \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION
                )
            );
        }

        return $this->pdo;
    }

    protected function applyPrefixed($sql)
    {
        return str_replace(static::TABLE_PREFIX, $this->getDatabaseConfig()->getPrefix(), $sql);
    }

    protected function getSqlPath($driver, $resource)
    {
        return $this->getMigrationsPath($driver) . DIRECTORY_SEPARATOR . $resource;
    }

    protected function getPhpClasses($filename)
    {
        $classes = $nsPos = $final = array();
        $foundNS = false;
        $ii      = 0;

        if (!file_exists($filename)) {
            return null;
        }

        $er = error_reporting();
        error_reporting(E_ALL ^ E_NOTICE);

        $php_code = file_get_contents($filename);
        $tokens   = token_get_all($php_code);
        $count    = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (!$foundNS && $tokens[$i][0] == T_NAMESPACE) {
                $nsPos[$ii]['start'] = $i;
                $foundNS             = true;
            } elseif ($foundNS && ($tokens[$i] == ';' || $tokens[$i] == '{')) {
                $nsPos[$ii]['end'] = $i;
                $ii++;
                $foundNS = false;
            } elseif ($i - 2 >= 0 && $tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                if ($i - 4 >= 0 && $tokens[$i - 4][0] == T_ABSTRACT) {
                    $classes[$ii][] = array(
                        'name' => $tokens[$i][1],
                        'type' => 'ABSTRACT CLASS'
                    );
                } else {
                    $classes[$ii][] = array(
                        'name' => $tokens[$i][1],
                        'type' => 'CLASS'
                    );
                }
            } elseif ($i - 2 >= 0 && $tokens[$i - 2][0] == T_INTERFACE && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                $classes[$ii][] = array(
                    'name' => $tokens[$i][1],
                    'type' => 'INTERFACE'
                );
            }
        }
        error_reporting($er);
        if (empty($classes)) {
            return null;
        }

        if (!empty($nsPos)) {
            foreach ($nsPos as $k => $p) {
                $ns = '';
                for ($i = $p['start'] + 1; $i < $p['end']; $i++) {
                    $ns .= $tokens[$i][1];
                }

                $ns        = trim($ns);
                $final[$k] = array(
                    'namespace' => $ns,
                    'classes'   => $classes[$k + 1]
                );
            }
            $classes = $final;
        }

        return $classes;
    }

    protected function getMaxAvailablePatchTag()
    {
        $files = array_keys($this->getAvailableMigrationFiles());

        return end($files);
    }

    /**
     * @return array|null
     * @throws \FamGeneTree\SetupBundle\Context\Migration\Exception\MigrationException
     */
    protected function getAvailableMigrationFiles()
    {
        if (null === $this->availableMigrationFiles) {
            $path                          = $this->getMigrationsPath($this->databaseConfig->getDbSystem());
            $this->availableMigrationFiles = [];
            foreach (scandir($path) as $file) {
                if (preg_match(static::MIGRATION_FILENAME_PATTERN, $file, $matches)) {
                    if (isset($this->availableMigrationFiles[$matches[1]])) {
                        throw new MigrationException("Migration tag '{$matches[1]}' is defined twice. I don't know what to do. Inform the developer and wait for a bug fix.");
                    }
                    $this->availableMigrationFiles[$matches[1]] = $path . DIRECTORY_SEPARATOR . $file;
                }
            }
            asort($this->availableMigrationFiles);
        }

        return $this->availableMigrationFiles;
    }

    /**
     * @param $dbSystem
     *
     * @return string
     */
    protected function getMigrationsPath($dbSystem)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/../Plan/Step/' . $dbSystem . '/migrations');
    }

    /**
     * @param $dbSystem
     *
     * @return string
     */
    protected function getWtMigrationsPath($dbSystem)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/../Plan/Step/' . $dbSystem . '/wt_migrations');
    }

    protected function isWebtreesDatabase()
    {
        try {
            $pdo       = $this->getConnection();
            $sql       = $this->applyPrefixed("SELECT setting_value FROM `###PREFIX###site_setting` WHERE  `setting_name` = 'WT_SCHEMA_VERSION' ;");
            $statement = $pdo->query($sql);
            $result    = $statement->fetch(\PDO::FETCH_COLUMN);
            if (false === $result) {
                return false;
            } elseif ($result > 1) {
                return true;
            } else {
                return false;
            }
        } catch (\PDOException $ex) {
            return false;
        }
    }

    /**
     * @return \FamGeneTree\SetupBundle\Context\Migration\Plan\MigrationPlan
     */
    protected function createMigrationPlanForWebtreesDb()
    {
        return new MigrationPlan(Migrate::DB_SYSTEM_MYSQL, $this->getConnection());
    }

    /**
     * @return \FamGeneTree\SetupBundle\Context\Migration\Plan\MigrationPlan
     */
    protected function createMigrationPlanForEmptyDb()
    {
        $files = $this->getAvailableMigrationFiles();
        $plan  = new MigrationPlan($this->databaseConfig->getDbSystem(), $this->getConnection());
        foreach ($files as $patchId => $file) {
            $plan->addMigration($this->createPlanStep($file, $patchId));
        }

        return $plan;
    }

    /**
     * @param string $currentTag
     *
     * @return \FamGeneTree\SetupBundle\Context\Migration\Plan\MigrationPlan
     */
    protected function createMigrationPlan($currentTag)
    {
        $files = $this->getAvailableMigrationFiles();
        $plan  = new MigrationPlan($this->databaseConfig->getDbSystem(), $this->getConnection());
        foreach ($files as $patchId => $file) {
            if ($patchId > $currentTag) {
                $plan->addMigration($this->createPlanStep($file, $patchId));
            }
        }

        return $plan;
    }

    /**
     * @param string $file
     * @param string $patchId
     *
     * @return \FamGeneTree\SetupBundle\Context\Migration\Plan\Step\MigrationPlanSqlStep
     * @throws \FamGeneTree\SetupBundle\Context\Migration\Exception\MigrationPlanStepClassNotFound
     */
    protected function createPlanStep($file, $patchId)
    {
        if (preg_match('/\.php$/i', $file)) {
            $classes = $this->getPhpClasses($file);
            if (empty($classes)) {
                throw new MigrationPlanStepClassNotFound("No class found in {$file}, but needed MigrationPlanAbstract derived class there.");
            }
            $class = '\\' . $classes[0]['namespace'] . '\\' . $classes[0]['classes'][0]['name'];
            require_once "$file";

            return new $class($this->getConnection(), $this->getDatabaseConfig()->getPrefix(), $patchId);
        } else {
            return new MigrationPlanSqlStep($this->getConnection(), $this->getDatabaseConfig()
                                                                         ->getPrefix(), $patchId, $file);
        }
    }
}