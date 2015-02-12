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

// Only record hits for certain pages
use Fgt\Globals;

switch (WT_SCRIPT_NAME) {
    case 'index.php':
        switch (Filter::get('ctype', 'gedcom|user', Auth::check() ? 'user' : 'gedcom')) {
            case 'user':
                $page_parameter = 'user:' . Auth::check();
                break;
            case 'gedcom':
                $page_parameter = 'gedcom:' . WT_GED_ID;
                break;
            default:
                $page_parameter = '';
                break;
        }
        break;
    case 'individual.php':
        $page_parameter = Filter::get('pid', WT_REGEX_XREF);
        break;
    case 'family.php':
        $page_parameter = Filter::get('famid', WT_REGEX_XREF);
        break;
    case 'source.php':
        $page_parameter = Filter::get('sid', WT_REGEX_XREF);
        break;
    case 'repo.php':
        $page_parameter = Filter::get('rid', WT_REGEX_XREF);
        break;
    case 'note.php':
        $page_parameter = Filter::get('nid', WT_REGEX_XREF);
        break;
    case 'mediaviewer.php':
        $page_parameter = Filter::get('mid', WT_REGEX_XREF);
        break;
    default:
        $page_parameter = '';
        break;
}
if ($page_parameter) {
    $hitCount = Database::prepare(
        "SELECT page_count FROM `##hit_counter`" .
        " WHERE gedcom_id=? AND page_name=? AND page_parameter=?"
    )
                        ->execute(array(
                                      WT_GED_ID,
                                      WT_SCRIPT_NAME,
                                      $page_parameter
                                  ))
                        ->fetchOne();

    // Only record one hit per session
    if ($page_parameter && empty(Globals::i()->WT_SESSION->SESSION_PAGE_HITS[WT_SCRIPT_NAME . $page_parameter])) {
        Globals::i()->WT_SESSION->SESSION_PAGE_HITS[WT_SCRIPT_NAME . $page_parameter] = true;
        if (is_null($hitCount)) {
            $hitCount = 1;
            Database::prepare(
                "INSERT INTO `##hit_counter` (gedcom_id, page_name, page_parameter, page_count) VALUES (?, ?, ?, ?)"
            )
                    ->execute(array(
                                  WT_GED_ID,
                                  WT_SCRIPT_NAME,
                                  $page_parameter,
                                  $hitCount
                              ));
        } else {
            $hitCount++;
            Database::prepare(
                "UPDATE `##hit_counter` SET page_count=?" .
                " WHERE gedcom_id=? AND page_name=? AND page_parameter=?"
            )
                    ->execute(array(
                                  $hitCount,
                                  WT_GED_ID,
                                  WT_SCRIPT_NAME,
                                  $page_parameter
                              ));
        }
    }
} else {
    $hitCount = 1;
}

Globals::i()->hitCount = '<span class="hit-counter">' . I18N::number($hitCount) . '</span>';

unset($page_name, $page_parameter, $hitCount);
