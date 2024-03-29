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

// Update the database schema from version 12 to 13
// - delete old config settings

// Convert MULTI_MEDIA (0=false, 1=true) to MEDIA_UPLOAD (1=members, 0=managers, -1=nobody)
try {
    Database::i()->exec("UPDATE `##gedcom_setting` SET setting_name='MEDIA_UPLOAD' WHERE setting_name='MULTI_MEDIA'");
} catch (PDOException $ex) {
    // This could theoretically cause a duplicate key error, if a MULTI_MEDIA setting already exists
}

// Remove old settings
Database::i()->exec("DELETE FROM `##gedcom_setting` WHERE setting_name IN ('SHOW_MEDIA_FILENAME', 'USE_THUMBS_MAIN', 'MULTI_MEDIA')");
Database::i()->exec("DELETE FROM `##default_resn` WHERE tag_type IN ('_PRIM')");

// Update the version to indicate success
Site::setPreference($schema_name, $next_version);
