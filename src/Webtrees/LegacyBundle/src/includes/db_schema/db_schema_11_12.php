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

// Update the database schema from version 11 to 12
// - delete the wt_name.n_list column; it has never been used
// - a bug in webtrees 1.1.2 caused the wt_name.n_full column
// to include slashes around the surname.  These are unnecessary,
// and cause problems when we try to match the name from the
// gedcom with the name from the table.
// Remove slashes from INDI names

Database::i()->exec("UPDATE `##name` SET n_full=REPLACE(n_full, '/', '') WHERE n_surn IS NOT NULL");

try {
    Database::i()->exec("ALTER TABLE `##name` DROP n_list");
} catch (PDOException $x) {
    // Already done?
}

// Update the version to indicate success
Site::setPreference($schema_name, $next_version);
