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

// Update the favorite module database schema from version 0 to version 1
//
// Version 0: empty database
// Version 1: create the tables, as per PGV 4.2.1
Database::i()->exec(
    "CREATE TABLE IF NOT EXISTS `##favorites` (" .
    " fv_id       INTEGER AUTO_INCREMENT NOT NULL," .
    " fv_username VARCHAR(32)            NOT NULL," .
    " fv_gid      VARCHAR(20)                NULL," .
    " fv_type     VARCHAR(15)                NULL," .
    " fv_file     VARCHAR(100)               NULL," .
    " fv_url      VARCHAR(255)               NULL," .
    " fv_title    VARCHAR(255)               NULL," .
    " fv_note     TEXT                       NULL," .
    " PRIMARY KEY (fv_id)," .
    "         KEY ix1 (fv_username)" .
    ") COLLATE utf8_unicode_ci ENGINE=InnoDB"
);

// Update the version to indicate success
Site::setPreference($schema_name, $next_version);
