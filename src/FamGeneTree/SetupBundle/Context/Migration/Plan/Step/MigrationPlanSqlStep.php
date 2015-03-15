<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\SetupBundle\Context\Migration\Plan\Step;

use FamGeneTree\SetupBundle\Context\Migration\Exception\MigrationException;

class MigrationPlanSqlStep extends MigrationStepAbstract
{

    public function __construct(\PDO $pdo, $prefix, $patchId, $sqlFile)
    {
        $this->sqlFile = $sqlFile;
        parent::__construct($pdo, $prefix, $patchId);
    }

    protected function executeStep()
    {
        $pdo = $this->getPdo();
        try {
            $sql = $this->applyPrefix(file_get_contents($this->sqlFile));
            $pdo->exec($sql);
        } catch (\PDOException $ex) {
            throw new MigrationException("Migration in file {$this->sqlFile} went wrong. Rolled back changes.", 1, $ex);
        }
    }
}