<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Step;

/**
 * Class DatabaseCreationStep
 *
 * @package FamGeneTree\SetupBundle\Context\Setup\Step
 * @author  Christoph Graupner <ch.graupner@workingdeveloper.net>
 */
class DatabaseCreationStep extends StepBase
{

    protected $databaseConfig = null;

    /**
     * @return null
     */
    public function getDatabaseConfig()
    {
        return $this->databaseConfig;
    }

    /**
     * @param null $databaseConfig
     */
    public function setDatabaseConfig($databaseConfig)
    {
        $this->databaseConfig = $databaseConfig;
    }

    public function run()
    {
        try {
            $srvMigrate = $this->container->get('fgt.migrate.service');
            $srvMigrate->setDatabaseConfig($this->getDatabaseConfig());
            $srvMigrate->executeMigrations();
            $this->addResult(
                new StepResult(
                    'Database Creation',
                    StepResult::STATE_SUCCESS
                )
            );
        } catch (\Exception $ex) {
            $this->addResult(
                new StepResult(
                    'Database Creation',
                    StepResult::STATE_FAILED,
                    $ex->getMessage()
                )
            );
        }
        return $this->isSuccess();
    }
}