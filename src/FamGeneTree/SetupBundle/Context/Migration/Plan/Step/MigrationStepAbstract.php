<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Migration\Plan\Step;

use FamGeneTree\SetupBundle\Context\Migration\Exception\MigrationException;

abstract class MigrationStepAbstract
{
    /**
     * @var \PDO
     */
    protected $pdo                 = null;
    protected $patchId             = null;
    protected $migrationLogMessage = null;
    protected $prefix              = null;

    public function __construct(\PDO $pdo, $prefix, $patchId)
    {
        $this->pdo     = $pdo;
        $this->prefix  = $prefix;
        $this->patchId = $patchId;
    }

    /**
     * @throws MigrationException
     */
    final public function execute()
    {
        try {
            $this->executeStep();
            $statement = $this->getPdo()
                              ->prepare($this->applyPrefix('INSERT INTO `###PREFIX###schema_updates` (`patch_id`,`messages`) VALUES (:patch_id,:message);'));
            $statement->execute(
                array(
                    ':patch_id' => $this->getPatchId(),
                    ':message'  => $this->getMigrationMessage()
                )
            );
        } catch (\PDOException $ex) {
            throw new MigrationException('Setting patch id failed.', 0, $ex);
        }
    }

    public function getPatchId()
    {
        return $this->patchId;
    }

    /**
     * @return bool successful
     * @throws MigrationException
     */
    abstract protected function executeStep();

    protected function getPdo()
    {
        return $this->pdo;
    }

    protected function applyPrefix($sql)
    {
        return str_replace('###PREFIX###', $this->prefix, $sql);
    }

    protected function getMigrationMessage()
    {
        return $this->migrationLogMessage;
    }
}