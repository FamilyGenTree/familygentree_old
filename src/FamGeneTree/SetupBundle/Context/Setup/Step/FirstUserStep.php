<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Step;

use FamGeneTree\SetupBundle\Context\Setup\Config\ConfigAbstract;
use FamGeneTree\SetupBundle\Context\Setup\Config\ConfigFirstUser;

class FirstUserStep extends StepBase
{

    /**
     * @param \FamGeneTree\SetupBundle\Context\Setup\Config\ConfigAbstract $config
     *
     * @return StepResultAggregate
     */
    public function checkConfig(ConfigAbstract $config)
    {
        // TODO: Implement checkConfig() method.
        $ret = new StepResultAggregate(
            __METHOD__
        );
        /** @var ConfigFirstUser $config */
        if ($config->getPassword() !== $config->getPasswordRepeat()) {
            $ret->addResult(
                new StepResult(
                    'Passwords do not match',

                    StepResult::STATE_FAILED
                )
            );
        }

        return $ret;
    }

    public function run()
    {
        //Database::i()->prepare(
        //    "INSERT IGNORE INTO `##user` (user_id, user_name, real_name, email, password) VALUES " .
        //    " (-1, 'DEFAULT_USER', 'DEFAULT_USER', 'DEFAULT_USER', 'DEFAULT_USER'), (1, ?, ?, ?, ?)"
        //)
        //        ->execute(array(
        //                      $_POST['wtuser'],
        //                      $_POST['wtname'],
        //                      $_POST['wtemail'],
        //                      password_hash($_POST['wtpass'], PASSWORD_DEFAULT)
        //                  ));
        //
        //Database::i()->prepare(
        //    "INSERT IGNORE INTO `##user_setting` (user_id, setting_name, setting_value) VALUES " .
        //    " (1, 'canadmin',          ?)," .
        //    " (1, 'language',          ?)," .
        //    " (1, 'verified',          ?)," .
        //    " (1, 'verified_by_admin', ?)," .
        //    " (1, 'auto_accept',       ?)," .
        //    " (1, 'visibleonline',     ?)"
        //)
        //        ->execute(array(
        //                      1,
        //                      WT_LOCALE,
        //                      1,
        //                      1,
        //                      0,
        //                      1
        //                  ));
        //
        //// Search for all installed modules, and enable them.
        //Module::getInstalledModules('enabled');
        //
        //// Create the blocks for the admin user
        //Database::i()->prepare(
        //    "INSERT INTO `##block` (user_id, location, block_order, module_name)" .
        //    " SELECT 1, location, block_order, module_name" .
        //    " FROM `##block`" .
        //    " WHERE user_id=-1"
        //)
        //        ->execute();
    }

    protected function applyPrefix($sql)
    {
        return str_replace(array(
                               '###PREFIX###',
                               '###COLATION###'
                           ), array(
                               $this->config->getPrefix(),
                               'utf8_unicode_ci'
                           ), $sql);
    }
}