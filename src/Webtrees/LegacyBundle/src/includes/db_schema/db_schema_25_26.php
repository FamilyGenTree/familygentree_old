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

// Update the database schema from version 25-26
// - delete unused settings and update indexes

Database::i()->exec(
    "DELETE FROM `##site_setting` WHERE setting_name IN ('WELCOME_TEXT_CUST_HEAD')"
);

// Modern versions of Internet Explorer use a different
Database::i()->exec(
    "INSERT IGNORE INTO `##site_access_rule` (user_agent_pattern, rule, comment) VALUES" .
    " ('Mozilla/% (Windows%; Trident%; rv:%) like Gecko', 'allow', 'Modern Internet Explorer')"
);

// Update the version to indicate success
Site::setPreference($schema_name, $next_version);
