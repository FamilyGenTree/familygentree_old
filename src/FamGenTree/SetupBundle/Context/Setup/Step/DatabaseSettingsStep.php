<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Context\Setup\Step;

use FamGenTree\AppBundle\Context\Configuration\Domain\SymfonyParameters\ParametersDatabase;
use FamGenTree\SetupBundle\Context\Setup\Config\ConfigAbstract;
use FamGenTree\SetupBundle\Context\Setup\Config\ConfigDatabase;

class DatabaseSettingsStep extends StepBase
{
    /**
     * @param \FamGenTree\SetupBundle\Context\Setup\Config\ConfigAbstract $config
     *
     * @return StepResultAggregate
     */
    public function checkConfig(ConfigAbstract $config)
    {
        $result = new StepResultAggregate('Database Settings');
        /** @var ConfigDatabase $config */
        if ('' == trim($config->getDbname())) {
            $result->addResult(
                new StepResult(
                    'Database name',
                    StepResult::STATE_FAILED,
                    'Database not specified'
                )
            );
        }
        $result->addResult($this->checkConnect($config));
        $result->addResult($this->checkDatabaseVersion($config));

        $result->addResult($this->checkCreateTable($config));

        return $result;
    }

    /**
     * @param \FamGenTree\SetupBundle\Context\Setup\Config\ConfigDatabase $config
     *
     * @return bool|\FamGenTree\SetupBundle\Context\Migration\MigrationReport
     */
    public function isMigrationNeeded(ConfigDatabase $config)
    {
        $srvMigrate = $this->container->get('fgt.migrate.service');
        $srvMigrate->setDatabaseConfig($config);
        if ($srvMigrate->isMigrationNeeded()) {
            $report = $srvMigrate->getMigrationPlan();

            return $report;
        }

        return false;
    }

    public function initialize($getConfigDatabase)
    {
    }

    public function run()
    {
        /** @var ConfigDatabase $config */
        $config        = $this->getConfig();
        $paramFactory  = $this->container->get('fgt.setup.configuration.parameters.factory');
        $symfonyParams = $paramFactory->loadParameters();
        $symfonyParams->mergeParams(
            new ParametersDatabase(
                $config->getDbSystemAsDbalString(),
                $config->getDbname(),
                $config->getUser(),
                $config->getPassword(),
                $config->getPrefix(),
                $config->getHost(),
                $config->getPort()
            )
        );
        $paramFactory->writeParameters($symfonyParams);
    }

    protected function checkConnect(ConfigDatabase $config)
    {
        $canConnect = false;
        $msg        = '';
        try {
            $pdo = $this->getPDO($config);

            $pdo->exec('SELECT 1');
            $canConnect = true;
        } catch (\PDOException $ex) {
            $canConnect = false;
            $msg .= "\n" . $ex->getMessage();
        }

        return new StepResult(
            'Can Connect DB',
            $canConnect ? StepResult::STATE_OK : StepResult::STATE_FAILED,
            'Could not connect with this credentials to the database.' . $msg
        );
    }

    protected function getPDO(ConfigDatabase $config)
    {
        $dsn = "{$config->getDbSystem()}:host={$config->getHost()};dbname={$config->getDbname()}";
        $pdo = new \PDO(
            $dsn,
            $config->getUser(),
            $config->getPassword(),
            array(
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE          => \PDO::ERRMODE_EXCEPTION
            )
        );

        return $pdo;
    }

    protected function checkCreateTable(ConfigDatabase $config)
    {
        $canCreate = true;

        return new StepResult(
            'Permissions',
            $canCreate ? StepResult::STATE_OK : StepResult::STATE_FAILED,
            'Could not create tables with this database user, but that is necessary.'
        );
    }

    protected function checkDatabaseVersion($config)
    {
        try {
            $pdo = $this->getPDO($config);
            $pdo->exec("SET NAMES 'utf8'");
            $col = $pdo->query("SHOW VARIABLES LIKE 'VERSION'")
                       ->fetchColumn(1);
            if (version_compare($col, '5.1', '<')) {
                return new StepResult(
                    'Database Version',
                    StepResult::STATE_FAILED,
                    sprintf('This database is only running MySQL version %s.  You cannot install %s here.', $col, 'FamGenTree')
                );
            } else {
                return new StepResult(
                    'Database Version',
                    StepResult::STATE_SUCCESS
                );
            }
        } catch (\PDOException $ex) {
            return new StepResult(
                'Database Version',
                StepResult::STATE_FAILED,
                sprintf('Can\'t query database for version. Reason: %s', $ex)
            );
        }
    }
}