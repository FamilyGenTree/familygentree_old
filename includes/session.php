<?php
namespace Fisharebest\Webtrees;

/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use Fgt\Application;

// WT_SCRIPT_NAME is defined in each script that the user is permitted to load.
if (!defined('WT_SCRIPT_NAME')) {
    http_response_code(403);

    return;
}

// We use some PHP5.5 features, but need to run on older servers
if (version_compare(PHP_VERSION, '5.4', '<')) {
    throw new \Exception('PHP version 5.4+ required. You have ' . PHP_VERSION . ' running. Can not run here.');
}

// We want to know about all PHP errors
error_reporting(E_ALL | E_STRICT);
if (strpos(ini_get('disable_functions'), 'ini_set') === false) {
    ini_set('display_errors', 'on');
}


// To embed webtrees code in other applications, we must explicitly declare any global variables that we create.
// most pages
global $controller;

// For performance, it is quicker to refer to files using absolute paths
define('WT_ROOT', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR);

require WT_ROOT . 'vendor/autoload.php';

Application::i()->init()
           ->started();

// These theme globals are horribly abused.
global $bwidth,$bheight,$basexoffset,$baseyoffset,$bxspacing,$byspacing,$Dbwidth,$Dbheight;

$bwidth      = Theme::theme()
                    ->parameter('chart-box-x');
$bheight     = Theme::theme()
                    ->parameter('chart-box-y');
$basexoffset = Theme::theme()
                    ->parameter('chart-offset-x');
$baseyoffset = Theme::theme()
                    ->parameter('chart-offset-y');
$bxspacing   = Theme::theme()
                    ->parameter('chart-spacing-x');
$byspacing   = Theme::theme()
                    ->parameter('chart-spacing-y');
$Dbwidth     = Theme::theme()
                    ->parameter('chart-descendancy-box-x');
$Dbheight    = Theme::theme()
                    ->parameter('chart-descendancy-box-y');
