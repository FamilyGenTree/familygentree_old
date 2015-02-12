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

define('WT_SCRIPT_NAME', 'logout.php');
Application::i()->init()->started();

if (Auth::id()) {
    Log::addAuthenticationLog('Logout: ' . Auth::user()
                                               ->getUserName() . '/' . Auth::user()
                                                                           ->getRealName());
    Auth::logout();
}

header('Location: ' . Config::get(Config::BASE_URL));
