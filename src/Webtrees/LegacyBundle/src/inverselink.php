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
use Fgt\Globals;

define('WT_SCRIPT_NAME', 'inverselink.php');
Application::i()->init()->started();

$controller = Application::i()->setActiveController(new SimpleController());
$controller
    ->restrictAccess(Auth::isEditor())
    ->setPageTitle(I18N::translate('Link to an existing media object'))
    ->addExternalJavascript(WT_STATIC_URL.WebtreesTheme::WT_AUTOCOMPLETE_JS_URL)
    ->addInlineJavascript('autocomplete();')
    ->pageHeader();

//-- page parameters and checking
$linktoid = Filter::get('linktoid', WT_REGEX_XREF);
$mediaid  = Filter::get('mediaid', WT_REGEX_XREF);
$linkto   = Filter::get('linkto', 'person|source|family|manage|repository|note');
$action   = Filter::get('action', 'choose|update', 'choose');

// If GedFAct_assistant/_MEDIA/ installed ======================
if ($linkto == 'manage' && array_key_exists('GEDFact_assistant', Module::getActiveModules())) {
    require WT_ROOT . WT_MODULES_DIR . 'GEDFact_assistant/_MEDIA/media_0_inverselink.php';
} else {

    //-- check for admin
    $paramok = true;
    if (!empty($linktoid)) {
        $paramok = GedcomRecord::getInstance($linktoid)
                               ->canShow();
    }

    if ($action == "choose" && $paramok) {
        echo '<form name="link" method="get" action="inverselink.php">';
        echo '<input type="hidden" name="action" value="update">';
        if (!empty($mediaid)) {
            echo '<input type="hidden" name="mediaid" value="', $mediaid, '">';
        }
        if (!empty($linktoid)) {
            echo '<input type="hidden" name="linktoid" value="', $linktoid, '">';
        }
        echo '<input type="hidden" name="linkto" value="', $linkto, '">';
        echo '<input type="hidden" name="ged" value="', Globals::i()->GEDCOM, '">';
        echo '<table class="facts_table">';
        echo '<tr><td class="topbottombar" colspan="2">';
        echo I18N::translate('Link to an existing media object');
        echo '</td></tr><tr><td class="descriptionbox width20 wrap">', I18N::translate('Media'), '</td>';
        echo '<td class="optionbox wrap">';
        if (!empty($mediaid)) {
            //-- Get the title of this existing Media item
            $title =
                Database::i()->prepare("SELECT m_titl FROM `##media` where m_id=? AND m_file=?")
                        ->execute(array(
                                      $mediaid,
                                      WT_GED_ID
                                  ))
                        ->fetchOne();
            if ($title) {
                echo '<b>', Filter::escapeHtml($title), '</b>';
            } else {
                echo '<b>', $mediaid, '</b>';
            }
        } else {
            echo '<input data-autocomplete-type="OBJE" type="text" name="mediaid" id="mediaid" size="5">';
            echo ' ', FunctionsPrint::i()->print_findmedia_link('mediaid', '1media');
            echo '</td></tr>';
        }

        if (!isset($linktoid)) {
            $linktoid = '';
        }
        echo '<tr><td class="descriptionbox">';

        if ($linkto == "person") {
            echo I18N::translate('Individual'), "</td>";
            echo '<td class="optionbox wrap">';
            if ($linktoid == "") {
                echo '<input class="pedigree_form" type="text" name="linktoid" id="linktopid" size="3" value="', $linktoid, '"> ';
                echo FunctionsPrint::i()->print_findindi_link('linktopid');
            } else {
                $record = Individual::getInstance($linktoid);
                echo $record->format_list('span', false, $record->getFullName());
            }
        }

        if ($linkto == "family") {
            echo I18N::translate('Family'), '</td>';
            echo '<td class="optionbox wrap">';
            if ($linktoid == "") {
                echo '<input class="pedigree_form" type="text" name="linktoid" id="linktofamid" size="3" value="', $linktoid, '"> ';
                echo FunctionsPrint::i()->print_findfamily_link('linktofamid');
            } else {
                $record = Family::getInstance($linktoid);
                echo $record->format_list('span', false, $record->getFullName());
            }
        }

        if ($linkto == "source") {
            echo I18N::translate('Source'), "</td>";
            echo '<td  class="optionbox wrap">';
            if ($linktoid == "") {
                echo '<input class="pedigree_form" type="text" name="linktoid" id="linktosid" size="3" value="', $linktoid, '"> ';
                echo FunctionsPrint::i()->print_findsource_link('linktosid');
            } else {
                $record = Source::getInstance($linktoid);
                echo $record->format_list('span', false, $record->getFullName());
            }
        }
        if ($linkto == "repository") {
            echo I18N::translate('Repository'), "</td>";
            echo '<td  class="optionbox wrap">';
            if ($linktoid == "") {
                echo '<input class="pedigree_form" type="text" name="linktoid" id="linktorid" size="3" value="', $linktoid, '">';
            } else {
                $record = Repository::getInstance($linktoid);
                echo $record->format_list('span', false, $record->getFullName());
            }
        }

        if ($linkto == "note") {
            echo I18N::translate('Shared note'), "</td>";
            echo '<td  class="optionbox wrap">';
            if ($linktoid == "") {
                echo '<input class="pedigree_form" type="text" name="linktoid" id="linktonid" size="3" value="', $linktoid, '">';
            } else {
                $record = Note::getInstance($linktoid);
                echo $record->format_list('span', false, $record->getFullName());
            }
        }

        echo '</td></tr>';
        echo '<tr><td class="topbottombar" colspan="2"><input type="submit" value="', I18N::translate('Set link'), '"></td></tr>';
        echo '</table>';
        echo '</form>';
    } elseif ($action == "update" && $paramok) {
        $record = GedcomRecord::getInstance($linktoid);
        $record->createFact('1 OBJE @' . $mediaid . '@', true);
        $controller->addInlineJavascript('closePopupAndReloadParent();');

    }
    echo '<button onclick="closePopupAndReloadParent();">', I18N::translate('close'), '</button>';
}
