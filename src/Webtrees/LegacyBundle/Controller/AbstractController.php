<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Webtrees\LegacyBundle\Controller;
use Fgt\Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


abstract class AbstractController extends Controller {
    protected function setConfig()
    {
        define('FGT_ROOT', dirname(__DIR__) . '/src');
// For performance, it is quicker to refer to files using absolute paths
        define('WT_ROOT', realpath(FGT_ROOT) . DIRECTORY_SEPARATOR);

        Config::set(Config::DATA_DIRECTORY, dirname(dirname(dirname(dirname(__DIR__)))) . '/data');
        Config::set(Config::CONFIG_PATH, Config::get(Config::DATA_DIRECTORY) . '/config.ini.php');
        Config::set(Config::CACHE, $this->get('webtrees.cache'));
        Config::set(Config::CACHE_DIR, Config::get(Config::DATA_DIRECTORY).'/cache');
        Config::set(Config::MODULES_DIR, WT_ROOT . 'modules_v3/');
    }

}