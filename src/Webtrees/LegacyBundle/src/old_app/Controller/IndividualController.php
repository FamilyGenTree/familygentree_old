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
use Zend_Session;

/**
 * Class IndividualController - Controller for the individual page
 */
class IndividualController extends GedcomRecordController
{
    public $name_count  = 0;
    public $total_names = 0;

    public $tabs;

    /**
     * Startup activity
     */
    function __construct()
    {
        $xref         = Filter::get('pid', WT_REGEX_XREF);
        $this->record = Individual::getInstance($xref);

        if (!$this->record && Globals::i()->WT_TREE->getPreference('USE_RIN')) {
            $rin          = FunctionsDbPhp::i()->find_rin_id($xref);
            $this->record = Individual::getInstance($rin);
        }

        parent::__construct();

        $this->tabs = Module::getActiveTabs();

        // If we can display the details, add them to the page header
        if ($this->record && $this->record->canShow()) {
            $this->setPageTitle($this->record->getFullName() . ' ' . $this->record->getLifespan());
        }
    }

    /**
     * Get significant information from this page, to allow other pages such as
     * charts and reports to initialise with the same records
     *
     * @return Individual
     */
    public function getSignificantIndividual()
    {
        if ($this->record) {
            return $this->record;
        }

        return parent::getSignificantIndividual();
    }

    /**
     * Get significant information from this page, to allow other pages such as
     * charts and reports to initialise with the same records
     *
     * @return Family
     */
    public function getSignificantFamily()
    {
        if ($this->record) {
            foreach ($this->record->getChildFamilies() as $family) {
                return $family;
            }
            foreach ($this->record->getSpouseFamilies() as $family) {
                return $family;
            }
        }

        return parent::getSignificantFamily();
    }

    /**
     * Handle AJAX requests - to generate the tab content
     */
    public function ajaxRequest()
    {
        // Search engines should not make AJAX requests
        if (Globals::i()->SEARCH_SPIDER) {
            http_response_code(403);
            exit;
        }

        // Initialise tabs
        $tab = Filter::get('module');

        // A request for a non-existant tab?
        if (array_key_exists($tab, $this->tabs)) {
            $mod = $this->tabs[$tab];
        } else {
            http_response_code(404);
            exit;
        }

        header("Content-Type: text/html; charset=UTF-8"); // AJAX calls do not have the meta tag headers and need this set
        header("X-Robots-Tag: noindex,follow"); // AJAX pages should not show up in search results, any links can be followed though

        Zend_Session::writeClose();

        echo $mod->getTabContent();

        if (Database::i()->isDebugSql()) {
            echo Database::i()->getQueryLog();
        }
    }

    /**
     * print information for a name record
     *
     * @param Fact $event the event object
     */
    public function printNameRecord(Fact $event)
    {
        $factrec = $event->getGedcom();

        // Create a dummy record, so we can extract the formatted NAME value from the event.
        $dummy        = new Individual(
            'xref',
            "0 @xref@ INDI\n1 DEAT Y\n" . $factrec,
            null,
            WT_GED_ID
        );
        $all_names    = $dummy->getAllNames();
        $primary_name = $all_names[0];

        $this->name_count++;
        if ($this->name_count > 1) {
            echo '<h3 class="name_two">', $dummy->getFullName(), '</h3>';
        } //Other names accordion element
        echo '<div class="indi_name_details';
        if ($event->isPendingDeletion()) {
            echo ' old';
        }
        if ($event->isPendingAddition()) {
            echo ' new';
        }
        echo '">';

        echo '<div class="name1">';
        echo '<dl><dt class="label">', I18N::translate('Name'), '</dt>';
        $dummy->setPrimaryName(0);
        echo '<dd class="field">', $dummy->getFullName();
        if ($this->name_count == 1) {
            if (Auth::isAdmin()) {
                $user = User::findByGenealogyRecord(Globals::i()->WT_TREE, $this->record);
                if ($user) {
                    echo '<span> - <a class="warning" href="admin_users.php?filter=' . Filter::escapeHtml($user->getUserName()) . '">' . Filter::escapeHtml($user->getUserName()) . '</a></span>';
                }
            }
        }
        if ($this->record->canEdit() && !$event->isPendingDeletion()) {
            echo "<div class=\"deletelink\"><a class=\"deleteicon\" href=\"#\" onclick=\"return delete_fact('" . I18N::translate('Are you sure you want to delete this fact?') . "', '" . $this->record->getXref() . "', '" . $event->getFactId() . "');\" title=\"" . I18N::translate('Delete this name') . "\"><span class=\"link_text\">" . I18N::translate('Delete this name') . "</span></a></div>";
            echo "<div class=\"editlink\"><a href=\"#\" class=\"editicon\" onclick=\"edit_name('" . $this->record->getXref() . "', '" . $event->getFactId() . "'); return false;\" title=\"" . I18N::translate('Edit name') . "\"><span class=\"link_text\">" . I18N::translate('Edit name') . "</span></a></div>";
        }
        echo '</dd>';
        echo '</dl>';
        echo '</div>';
        $ct = preg_match_all('/\n2 (\w+) (.*)/', $factrec, $nmatch, PREG_SET_ORDER);
        for ($i = 0; $i < $ct; $i++) {
            echo '<div>';
            $fact = $nmatch[$i][1];
            if ($fact != 'SOUR' && $fact != 'NOTE' && $fact != 'SPFX') {
                echo '<dl><dt class="label">', WT_Gedcom_Tag::getLabel($fact, $this->record), '</dt>';
                echo '<dd class="field">'; // Before using dir="auto" on this field, note that Gecko treats this as an inline element but WebKit treats it as a block element
                if (isset($nmatch[$i][2])) {
                    $name = Filter::escapeHtml($nmatch[$i][2]);
                    $name = str_replace('/', '', $name);
                    $name = preg_replace('/(\S*)\*/', '<span class="starredname">\\1</span>', $name);
                    switch ($fact) {
                        case 'TYPE':
                            echo WT_Gedcom_Code_Name::getValue($name, $this->record);
                            break;
                        case 'SURN':
                            // The SURN field is not necessarily the surname.
                            // Where it is not a substring of the real surname, show it after the real surname.
                            $surname = Filter::escapeHtml($primary_name['surname']);
                            if (strpos($primary_name['surname'], str_replace(',', ' ', $nmatch[$i][2])) !== false) {
                                echo '<span dir="auto">' . $surname . '</span>';
                            } else {
                                echo I18N::translate('%1$s (%2$s)', '<span dir="auto">' . $surname . '</span>', '<span dir="auto">' . $name . '</span>');
                            }
                            break;
                        default:
                            echo '<span dir="auto">' . $name . '</span>';
                            break;
                    }
                }
                echo '</dd>';
                echo '</dl>';
            }
            echo '</div>';
        }
        if (preg_match("/\n2 SOUR/", $factrec)) {
            echo '<div id="indi_sour" class="clearfloat">', FunctionsPrintFacts::i()->print_fact_sources($factrec, 2), '</div>';
        }
        if (preg_match("/\n2 NOTE/", $factrec)) {
            echo '<div id="indi_note" class="clearfloat">', FunctionsPrint::i()->print_fact_notes($factrec, 2), '</div>';
        }
        echo '</div>';
    }

    /**
     * print information for a sex record
     *
     * @param Fact $event the Event object
     */
    public function printSexRecord(Fact $event)
    {
        $sex = $event->getValue();
        if (empty($sex)) {
            $sex = 'U';
        }
        echo '<span id="sex" class="';
        if ($event->isPendingDeletion()) {
            echo 'old ';
        }
        if ($event->isPendingAddition()) {
            echo 'new ';
        }
        switch ($sex) {
            case 'M':
                echo 'male_gender"';
                if ($event->canEdit()) {
                    echo ' title="', I18N::translate('Male'), ' - ', I18N::translate('Edit'), '"';
                    echo ' onclick="edit_record(\'' . $this->record->getXref() . '\', \'' . $event->getFactId() . '\'); return false;">';
                } else {
                    echo ' title="', I18N::translate('Male'), '">';
                }
                break;
            case 'F':
                echo 'female_gender"';
                if ($event->canEdit()) {
                    echo ' title="', I18N::translate('Female'), ' - ', I18N::translate('Edit'), '"';
                    echo ' onclick="edit_record(\'' . $this->record->getXref() . '\', \'' . $event->getFactId() . '\'); return false;">';
                } else {
                    echo ' title="', I18N::translate('Female'), '">';
                }
                break;
            case 'U':
                echo 'unknown_gender"';
                if ($event->canEdit()) {
                    echo ' title="', I18N::translate_c('unknown gender', 'Unknown'), ' - ', I18N::translate('Edit'), '"';
                    echo ' onclick="edit_record(\'' . $this->record->getXref() . '\', \'' . $event->getFactId() . '\'); return false;">';
                } else {
                    echo ' title="', I18N::translate_c('unknown gender', 'Unknown'), '">';
                }
                break;
        }
        echo '</span>';
    }

    /**
     * get edit menu
     */
    function getEditMenu()
    {
        if (!$this->record || $this->record->isPendingDeletion()) {
            return null;
        }
        // edit menu
        $menu = new Menu(I18N::translate('Edit'), '#', 'menu-indi');

        // What behaviour shall we give the main menu?  If we leave it blank, the framework
        // will copy the first submenu - which may be edit-raw or delete.
        // As a temporary solution, make it edit the name
        $menu->setOnclick("return false;");
        if (WT_USER_CAN_EDIT) {
            foreach ($this->record->getFacts() as $fact) {
                if ($fact->getTag() === 'NAME' && $fact->canEdit()) {
                    $menu->setOnclick("return edit_name('" . $this->record->getXref() . "', '" . $fact->getFactId() . "');");
                    break;
                }
            }

            $submenu = new Menu(I18N::translate('Add a new name'), '#', 'menu-indi-addname');
            $submenu->setOnclick("return add_name('" . $this->record->getXref() . "');");
            $menu->addSubmenu($submenu);

            $has_sex_record = false;
            $submenu        = new Menu(I18N::translate('Edit gender'), '#', 'menu-indi-editsex');
            foreach ($this->record->getFacts() as $fact) {
                if ($fact->getTag() == 'SEX' && $fact->canEdit()) {
                    $submenu->setOnclick("return edit_record('" . $this->record->getXref() . "', '" . $fact->getFactId() . "');");
                    $has_sex_record = true;
                    break;
                }
            }
            if (!$has_sex_record) {
                $submenu->setOnclick("return add_new_record('" . $this->record->getXref() . "', 'SEX');");
            }
            $menu->addSubmenu($submenu);

            if (count($this->record->getSpouseFamilies()) > 1) {
                $submenu = new Menu(I18N::translate('Re-order families'), '#', 'menu-indi-orderfam');
                $submenu->setOnclick("return reorder_families('" . $this->record->getXref() . "');");
                $menu->addSubmenu($submenu);
            }
        }

        // delete
        if (WT_USER_CAN_EDIT) {
            $submenu = new Menu(I18N::translate('Delete'), '#', 'menu-indi-del');
            $submenu->setOnclick("return delete_individual('" . I18N::translate('Are you sure you want to delete “%s”?', Filter::escapeJs(strip_tags($this->record->getFullName()))) . "', '" . $this->record->getXref() . "');");
            $menu->addSubmenu($submenu);
        }

        // edit raw
        if (Auth::isAdmin()
            || WT_USER_CAN_EDIT
               && $this->record->getTree()
                               ->getPreference('SHOW_GEDCOM_RECORD')
        ) {
            $submenu = new Menu(I18N::translate('Edit raw GEDCOM'), '#', 'menu-indi-editraw');
            $submenu->setOnclick("return edit_raw('" . $this->record->getXref() . "');");
            $menu->addSubmenu($submenu);
        }

        // add to favorites
        if (array_key_exists('user_favorites', Module::getActiveModules())) {
            $submenu = new Menu(
            /* I18N: Menu option.  Add [the current page] to the list of favorites */
                I18N::translate('Add to favorites'),
                '#',
                'menu-indi-addfav'
            );
            $submenu->setOnclick("jQuery.post('module.php?mod=user_favorites&amp;mod_action=menu-add-favorite',{xref:'" . $this->record->getXref() . "'},function(){location.reload();})");
            $menu->addSubmenu($submenu);
        }

        return $menu;
    }

    /**
     * get the person box stylesheet class for the given person
     *
     * @param Individual $person
     *
     * @return string returns 'person_box', 'person_boxF', or 'person_boxNN'
     */
    function getPersonStyle($person)
    {
        switch ($person->getSex()) {
            case 'M':
                $class = 'person_box';
                break;
            case 'F':
                $class = 'person_boxF';
                break;
            default:
                $class = 'person_boxNN';
                break;
        }
        if ($person->isPendingDeletion()) {
            $class .= ' old';
        } elseif ($person->isPendingAddtion()) {
            $class .= ' new';
        }

        return $class;
    }

    /**
     * Get significant information from this page, to allow other pages such as
     * charts and reports to initialise with the same records
     *
     * @return string
     */
    public function getSignificantSurname()
    {
        if ($this->record) {
            list($surn) = explode(',', $this->record->getSortname());

            return $surn;
        } else {
            return '';
        }
    }

    /**
     * Get the contents of sidebar.
     *
     * @return string
     */
    public function getSideBarContent()
    {
        $controller = Application::i()->getActiveController();

        $html   = '';
        $active = 0;
        $n      = 0;
        foreach (Module::getActiveSidebars() as $mod) {
            if ($mod->hasSidebarContent()) {
                $html .= '<h3 id="' . $mod->getName() . '"><a href="#">' . $mod->getTitle() . '</a></h3>';
                $html .= '<div id="sb_content_' . $mod->getName() . '">' . $mod->getSidebarContent() . '</div>';
                // The family navigator should be opened by default
                if ($mod->getName() == 'family_nav') {
                    $active = $n;
                }
                ++$n;
            }
        }

        if ($html) {
            $controller
                ->addInlineJavascript('
				jQuery("#sidebarAccordion").accordion({
					active:' . $active . ',
					heightStyle: "content",
					collapsible: true,
				});
			');

            return '<div id="sidebar"><div id="sidebarAccordion">' . $html . '</div></div>';
        } else {
            return '';
        }
    }
}
