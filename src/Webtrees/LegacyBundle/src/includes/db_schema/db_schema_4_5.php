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

// Update the database schema from version 4 to version 5
// - add support for sorting gedcoms non-alphabetically
//
// Also clean out some old/unused values and files.

try {
    Database::i()->exec("ALTER TABLE `##gedcom` ADD COLUMN sort_order INTEGER NOT NULL DEFAULT 0");
} catch (PDOException $ex) {
    // If this fails, it has probably already been done.
}

try {
    Database::i()->exec("ALTER TABLE `##gedcom` ADD INDEX ix1 (sort_order)");
} catch (PDOException $ex) {
    // If this fails, it has probably already been done.
}

// No longer used
Database::i()->exec("DELETE FROM `##gedcom_setting` WHERE setting_name IN ('PAGE_AFTER_LOGIN')");

// Change of defaults - do not add ASSO, etc. to NOTE objects
Database::i()->exec("UPDATE `##gedcom_setting` SET setting_value='SOUR' WHERE setting_value='ASSO,SOUR,NOTE,REPO' AND setting_name='NOTE_FACTS_ADD'");

// Update the version to indicate success
Site::setPreference($schema_name, $next_version);
