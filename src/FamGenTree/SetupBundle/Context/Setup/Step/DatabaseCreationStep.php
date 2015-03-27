<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGenTree\SetupBundle\Context\Setup\Step;

/**
 * Class DatabaseCreationStep
 *
 * @package FamGenTree\SetupBundle\Context\Setup\Step
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
        $srvMigrate = $this->container->get('fgt.migrate.service');
        try {
            $srvMigrate->setDatabaseConfig($this->getDatabaseConfig());
            $srvMigrate->executeMigrations();
        } catch (\Exception $ex) {
        }
        foreach ($srvMigrate->getResults()->getResults() as $result) {
            $this->addResult($result);
        }

        return $this->isSuccess();
    }
}