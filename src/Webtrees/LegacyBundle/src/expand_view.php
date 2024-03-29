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
use Zend_Session;

define('WT_SCRIPT_NAME', 'expand_view.php');
Application::i()->init()->started();

Zend_Session::writeClose();

header('Content-Type: text/html; charset=UTF-8');
$person = Individual::getInstance(Filter::get('pid', WT_REGEX_XREF));
if (!$person || !$person->canShow()) {
    return I18N::translate('Private');
}

$facts = $person->getFacts();
foreach ($person->getSpouseFamilies() as $family) {
    foreach ($family->getFacts() as $fact) {
        $facts[] = $fact;
    }
}
Functions::i()->sort_facts($facts);

foreach ($facts as $event) {
    switch ($event->getTag()) {
        case 'SEX':
        case 'FAMS':
        case 'FAMC':
        case 'NAME':
        case 'TITL':
        case 'NOTE':
        case 'SOUR':
        case 'SSN':
        case 'OBJE':
        case 'HUSB':
        case 'WIFE':
        case 'CHIL':
        case 'ALIA':
        case 'ADDR':
        case 'PHON':
        case 'SUBM':
        case '_EMAIL':
        case 'CHAN':
        case 'URL':
        case 'EMAIL':
        case 'WWW':
        case 'RESI':
        case 'RESN':
        case '_UID':
        case '_TODO':
        case '_WT_OBJE_SORT':
            // Do not show these
            break;
        case 'ASSO':
            // Associates
            echo FunctionsPrint::i()->format_asso_rela_record($event);
            break;
        default:
            // Simple version of FunctionsPrintFacts::i()->print_fact()
            echo $event->summary();
            break;
    }
}
