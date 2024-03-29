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

/**
 * Class sources_tab_WT_Module
 */
class sources_tab_WT_Module extends Module implements ModuleTabInterface
{
    private $facts;

    /** {@inheritdoc} */
    public function getTitle()
    {
        return /* I18N: Name of a module */
            I18N::translate('Sources');
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return /* I18N: Description of the “Sources” module */
            I18N::translate('A tab showing the sources linked to an individual.');
    }

    /** {@inheritdoc} */
    public function defaultTabOrder()
    {
        return 30;
    }

    /** {@inheritdoc} */
    public function hasTabContent()
    {
        return WT_USER_CAN_EDIT || $this->getFactsWithSources();
    }

    /** {@inheritdoc} */
    public function isGrayedOut()
    {
        return !$this->getFactsWithSources();
    }

    /** {@inheritdoc} */
    public function getTabContent()
    {
        $controller = Application::i()->getActiveController();

        ob_start();
        ?>
        <table class="facts_table">
            <tr>
                <td colspan="2" class="descriptionbox rela">
                    <input id="checkbox_sour2"
                           type="checkbox" <?php echo Globals::i()->WT_TREE->getPreference('SHOW_LEVEL2_NOTES')
                        ? 'checked' : ''; ?> onclick="jQuery('tr.row_sour2').toggle();">
                    <label
                        for="checkbox_sour2"><?php echo I18N::translate('Show all sources'), FunctionsPrint::i()->help_link('show_fact_sources'); ?></label>
                </td>
            </tr>
            <?php
            foreach ($this->getFactsWithSources() as $fact) {
                if ($fact->getTag() == 'SOUR') {
                    FunctionsPrintFacts::i()->print_main_sources($fact, 1);
                } else {
                    FunctionsPrintFacts::i()->print_main_sources($fact, 2);
                }
            }
            if (!$this->getFactsWithSources()) {
                echo '<tr><td id="no_tab4" colspan="2" class="facts_value">', I18N::translate('There are no source citations for this individual.'), '</td></tr>';
            }

            // New Source Link
            if ($controller->record->canEdit()) {
                ?>
                <tr>
                    <td class="facts_label">
                        <?php echo WT_Gedcom_Tag::getLabel('SOUR'); ?>
                    </td>
                    <td class="facts_value">
                        <a href="#"
                           onclick="add_new_record('<?php echo $controller->record->getXref(); ?>','SOUR'); return false;">
                            <?php echo I18N::translate('Add a new source citation'); ?>
                        </a>
                        <?php echo FunctionsPrint::i()->help_link('add_source'); ?>
                    </td>
                </tr>
            <?php
            }
            ?>
        </table>
        <?php
        if (!Globals::i()->WT_TREE->getPreference('SHOW_LEVEL2_NOTES')) {
            echo '<script>jQuery("tr.row_sour2").toggle();</script>';
        }

        return '<div id="' . $this->getName() . '_content">' . ob_get_clean() . '</div>';
    }

    /**
     * Get all the facts for an individual which contain sources.
     *
     * @return Fact[]
     */
    private function getFactsWithSources()
    {
        $controller = Application::i()->getActiveController();

        if ($this->facts === null) {
            $facts = $controller->record->getFacts();
            foreach ($controller->record->getSpouseFamilies() as $family) {
                if ($family->canShow()) {
                    foreach ($family->getFacts() as $fact) {
                        $facts[] = $fact;
                    }
                }
            }
            $this->facts = array();
            foreach ($facts as $fact) {
                if (preg_match('/(?:^1|\n\d) SOUR/', $fact->getGedcom())) {
                    $this->facts[] = $fact;
                }
            }
            Functions::i()->sort_facts($this->facts);
        }

        return $this->facts;
    }

    /** {@inheritdoc} */
    public function canLoadAjax()
    {
        return !Globals::i()->SEARCH_SPIDER; // Search engines cannot use AJAX
    }

    /** {@inheritdoc} */
    public function getPreLoadContent()
    {
        return '';
    }
}
