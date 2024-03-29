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

use PDOException;

// Update the database schema from version 29-30
// - delete an old/unused table

// Originally migrated from PGV, but never used.
try {
    Database::i()->exec("DROP TABLE `##ip_address`");
} catch (PDOException $ex) {
    // Already deleted?
}

// No longer used
Database::i()->exec("DELETE FROM `##user_setting` WHERE setting_name in ('edit_account')");

// https://bugs.launchpad.net/webtrees/+bug/1405672
Database::i()->exec(
    "UPDATE `##site_access_rule` SET user_agent_pattern = 'Mozilla/5.0 (% Konqueror/%'" .
    " WHERE user_agent_pattern='Mozilla/5.0 (compatible; Konqueror/%'"
);

// Update the version to indicate success
Site::setPreference($schema_name, $next_version);
