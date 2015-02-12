<?php
namespace Webtrees\LegacyBundle\Legacy;

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
use Fgt\Config;

define('WT_SCRIPT_NAME', 'module.php');
Application::i()->init()->started();

$all_modules = Module::getActiveModules();
$mod         = Filter::get('mod');
$mod_action  = Filter::get('mod_action');

if ($mod && array_key_exists($mod, $all_modules)) {
    $module = $all_modules[$mod];
    $module->modAction($mod_action);
} else {
    header('Location: ' . Config::get(Config::BASE_URL));
}
