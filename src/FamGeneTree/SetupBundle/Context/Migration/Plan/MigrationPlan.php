<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Migration\Plan;

use FamGeneTree\SetupBundle\Context\Migration\Plan\Step\MigrationStepAbstract;
use FamGeneTree\SetupBundle\Context\Setup\Step\StepResult;
use FamGeneTree\SetupBundle\Context\Setup\Step\StepResultAggregate;

class MigrationPlan
{
    protected $dbSystem;
    protected $migrations = array();
    /**
     * @var StepResultAggregate
     */
    protected $results = null;

    public function __construct($dbSystem, \PDO $connection)
    {
        $this->dbSystem = $dbSystem;
        $this->pdo      = $connection;
    }

    /**
     *
     */
    public function execute()
    {
        $this->results = new StepResultAggregate('Migration');
        /** @var MigrationStepAbstract $migration */
        try {
            $this->pdo->beginTransaction();
            foreach ($this->migrations as $migration) {
                try {
                    $migration->execute();
                    $this->results->addResult(
                        new StepResult(
                            $migration->getPatchId(),
                            StepResult::STATE_SUCCESS
                        )
                    );
                } catch (\Exception $ex) {
                    $this->results->addResult(
                        new StepResult(
                            $migration->getPatchId(),
                            StepResult::STATE_FAILED,
                            $ex
                        )
                    );
                    throw $ex;
                }
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
    public function getSteps()
    {
        return $this->migrations;
    }

    /**
     * @return StepResultAggregate
     */
    public function getResults()
    {
        return $this->results;
    }
}