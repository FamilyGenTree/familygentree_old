<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Step;

use FamGeneTree\SetupBundle\Context\Setup\Config\ConfigAbstract;
use FamGeneTree\SetupBundle\Context\Setup\Config\ConfigDatabase;

class DatabaseSetup extends StepBase
{

    /**
     * @param \FamGeneTree\SetupBundle\Context\Setup\Config\ConfigAbstract $config
     *
     * @return StepResultAggregate
     */
    public function checkConfig(ConfigAbstract $config)
    {
        $result = new StepResultAggregate('Database Settings');
        $result->addResult($this->checkConnect($config));

        $result->addResult($this->checkCreateTable($config));

        return $result;
    }

    protected function checkConnect(ConfigDatabase $config)
    {
        $canConnect = false;

        return new StepResult(
            'Can Connect DB',
            $canConnect ? StepResult::STATE_OK : StepResult::STATE_FAILED,
            'Could not connect with this credentials to the database.'
        );
    }

    private function checkCreateTable(ConfigDatabase $config)
    {
        $canCreate = false;

        return new StepResult(
            'Permissions',
            $canCreate ? StepResult::STATE_OK : StepResult::STATE_FAILED,
            'Could not create tables with this database user, but that is necessary.'
        );
    }
}