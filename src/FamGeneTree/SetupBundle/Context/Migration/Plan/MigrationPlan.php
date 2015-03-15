<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Migration\Plan;

use FamGeneTree\SetupBundle\Context\Migration\Plan\Step\MigrationStepAbstract;

class MigrationPlan
{
    protected $dbSystem;
    protected $migrations = array();

    public function __construct($dbSystem, \PDO $connection)
    {
        $this->dbSystem = $dbSystem;
        $this->pdo = $connection;
    }

    /**
     *
     */
    public function execute()
    {
        /** @var MigrationStepAbstract $migration */
        try {
            $this->pdo->beginTransaction();
            foreach ($this->migrations as $migration) {
                $migration->execute();
            }
            $this->pdo->commit();
        } catch (\Exception $ex) {
            $this->pdo->rollBack();
        }
    }

    public function getPlanDescription()
    {
        $ret = array();
        /** @var MigrationStepAbstract $migration */
        foreach ($this->migrations as $migration) {
            $ret[] = $migration->getPatchId();
        }

        return $ret;
    }

    public function addMigration(MigrationStepAbstract $step)
    {
        $this->migrations[] = $step;
    }

    /**
     * @return MigrationStepAbstract[]
     */
    public function getSteps() {
        return $this->migrations;
    }
}