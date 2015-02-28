<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Step\Database;

use FamGeneTree\SetupBundle\Context\Setup\Config\ConfigDatabase;
use Webtrees\LegacyBundle\Legacy\Database;
use Webtrees\LegacyBundle\Legacy\Module;

class DatabaseCreator
{

    /**
     * @var \PDO
     */
    protected $pdo = null;
    /**
     * @var ConfigDatabase
     */
    protected $config = null;

    public function create(ConfigDatabase $config)
    {
    }

    protected function oldDb()
    {
////////////////////////////////////////////////////////////////////////////////
// Step six  We have a database connection and a writable folder.  Do it!
////////////////////////////////////////////////////////////////////////////////

        try {
            // These shouldn’t fail.
            $this->execSchemaFile();
            $this->insertInitialData();

        } catch (\PDOException $ex) {
            echo
            '<p class="bad">', I18N::translate('An unexpected database error occurred.'), '</p>',
            '<pre>', $ex->getMessage(), '</pre>',
            '<p class="info">', I18N::translate('The webtrees developers would be very interested to learn about this error.  If you contact them, they will help you resolve the problem.'), '</p>';
        }
    }

    protected function execSchemaFile()
    {
    }


    protected function insertInitialData()
    {
        Database::i()->exec(
            "INSERT IGNORE INTO `##site_access_rule` (user_agent_pattern, rule, comment) VALUES" .
            " ('Mozilla/5.0 (%) Gecko/% %/%', 'allow', 'Gecko-based browsers')," .
            " ('Mozilla/5.0 (%) AppleWebKit/% (KHTML, like Gecko)%', 'allow', 'WebKit-based browsers')," .
            " ('Opera/% (%) Presto/% Version/%', 'allow', 'Presto-based browsers')," .
            " ('Mozilla/% (compatible; MSIE %', 'allow', 'Trident-based browsers')," .
            " ('Mozilla/% (Windows%; Trident%; rv:%) like Gecko', 'allow', 'Modern Internet Explorer')," .
            " ('Mozilla/5.0 (% Konqueror/%', 'allow', 'Konqueror browser')"
        );

        Database::i()->prepare(
            "INSERT IGNORE INTO `##gedcom` (gedcom_id, gedcom_name) VALUES " .
            " (-1, 'DEFAULT_TREE')"
        )
                ->execute();

        Database::i()->prepare(
            "INSERT IGNORE INTO `##user` (user_id, user_name, real_name, email, password) VALUES " .
            " (-1, 'DEFAULT_USER', 'DEFAULT_USER', 'DEFAULT_USER', 'DEFAULT_USER'), (1, ?, ?, ?, ?)"
        )
                ->execute(array(
                              $_POST['wtuser'],
                              $_POST['wtname'],
                              $_POST['wtemail'],
                              password_hash($_POST['wtpass'], PASSWORD_DEFAULT)
                          ));

        Database::i()->prepare(
            "INSERT IGNORE INTO `##user_setting` (user_id, setting_name, setting_value) VALUES " .
            " (1, 'canadmin',          ?)," .
            " (1, 'language',          ?)," .
            " (1, 'verified',          ?)," .
            " (1, 'verified_by_admin', ?)," .
            " (1, 'auto_accept',       ?)," .
            " (1, 'visibleonline',     ?)"
        )
                ->execute(array(
                              1,
                              WT_LOCALE,
                              1,
                              1,
                              0,
                              1
                          ));

        Database::i()->prepare(
            "INSERT IGNORE INTO `##site_setting` (setting_name, setting_value) VALUES " .
            "('WT_SCHEMA_VERSION',               '-2')," .
            "('INDEX_DIRECTORY',                 'data/')," .
            "('USE_REGISTRATION_MODULE',         '1')," .
            "('REQUIRE_ADMIN_AUTH_REGISTRATION', '1')," .
            "('ALLOW_USER_THEMES',               '1')," .
            "('ALLOW_CHANGE_GEDCOM',             '1')," .
            "('SESSION_TIME',                    '7200')," .
            "('SMTP_ACTIVE',                     'internal')," .
            "('SMTP_HOST',                       'localhost')," .
            "('SMTP_PORT',                       '25')," .
            "('SMTP_AUTH',                       '1')," .
            "('SMTP_AUTH_USER',                  '')," .
            "('SMTP_AUTH_PASS',                  '')," .
            "('SMTP_SSL',                        'none')," .
            "('SMTP_HELO',                       ?)," .
            "('SMTP_FROM_NAME',                  ?)"
        )
                ->execute(array(
                              $_SERVER['SERVER_NAME'],
                              $_SERVER['SERVER_NAME']
                          ));

        // Search for all installed modules, and enable them.
        Module::getInstalledModules('enabled');

        // Create the default settings for new users/family trees
        Database::i()->prepare(
            "INSERT INTO `##block` (user_id, location, block_order, module_name) VALUES (-1, 'main', 1, 'todays_events'), (-1, 'main', 2, 'user_messages'), (-1, 'main', 3, 'user_favorites'), (-1, 'side', 1, 'user_welcome'), (-1, 'side', 2, 'random_media'), (-1, 'side', 3, 'upcoming_events'), (-1, 'side', 4, 'logged_in')"
        )
                ->execute();
        Database::i()->prepare(
            "INSERT INTO `##block` (gedcom_id, location, block_order, module_name) VALUES (-1, 'main', 1, 'gedcom_stats'), (-1, 'main', 2, 'gedcom_news'), (-1, 'main', 3, 'gedcom_favorites'), (-1, 'main', 4, 'review_changes'), (-1, 'side', 1, 'gedcom_block'), (-1, 'side', 2, 'random_media'), (-1, 'side', 3, 'todays_events'), (-1, 'side', 4, 'logged_in')"
        )
                ->execute();
        // Create the blocks for the admin user
        Database::i()->prepare(
            "INSERT INTO `##block` (user_id, location, block_order, module_name)" .
            " SELECT 1, location, block_order, module_name" .
            " FROM `##block`" .
            " WHERE user_id=-1"
        )
                ->execute();
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