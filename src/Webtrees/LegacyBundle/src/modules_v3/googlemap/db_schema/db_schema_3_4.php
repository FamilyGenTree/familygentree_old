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

// Update the GM module database schema from version 3 to version 4
//
// Combine the two ways of enabling the GM module

Database::i()->exec(
    "UPDATE `##module` m, `##module_setting` ms SET m.status=CASE WHEN (m.status=1 AND ms.setting_value=1) THEN 'enabled' ELSE 'disabled' END WHERE m.module_name=ms.module_name AND m.module_name='googlemap' AND ms.setting_name='GM_ENABLED'"
);

Database::i()->exec(
    "DELETE FROM `##module_setting` WHERE module_name='googlemap' AND setting_name='GM_ENABLED'"
);

// Update the version to indicate success
Site::setPreference($schema_name, $next_version);
