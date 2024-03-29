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
 * Class media_WT_Module
 */
class media_WT_Module extends Module implements ModuleTabInterface
{
    private $facts;

    /** {@inheritdoc} */
    public function getTitle()
    {
        return /* I18N: Name of a module */
            I18N::translate('Media');
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return /* I18N: Description of the “Media” module */
            I18N::translate('A tab showing the media objects linked to an individual.');
    }

    /** {@inheritdoc} */
    public function defaultTabOrder()
    {
        return 50;
    }

    /** {@inheritdoc} */
    public function hasTabContent()
    {
        return WT_USER_CAN_EDIT || $this->getFactsWithMedia();
    }

    /** {@inheritdoc} */
    public function isGrayedOut()
    {
        return !$this->getFactsWithMedia();
    }

    /** {@inheritdoc} */
    public function getTabContent()
    {
        $controller = Application::i()->getActiveController();

        ob_start();
        echo '<table class="facts_table">';
        foreach ($this->getFactsWithMedia() as $fact) {
            if ($fact->getTag() == 'OBJE') {
                FunctionsPrintFacts::i()->print_main_media($fact, 1);
            } else {
                for ($i = 2; $i < 4; ++$i) {
                    FunctionsPrintFacts::i()->print_main_media($fact, $i);
                }
            }
        }
        if (!$this->getFactsWithMedia()) {
            echo '<tr><td id="no_tab4" colspan="2" class="facts_value">', I18N::translate('There are no media objects for this individual.'), '</td></tr>';
        }
        // New media link
        if ($controller->record->canEdit() && Globals::i()->WT_TREE->getPreference('MEDIA_UPLOAD') >= WT_USER_ACCESS_LEVEL) {
            ?>
            <tr>
                <td class="facts_label">
                    <?php echo WT_Gedcom_Tag::getLabel('OBJE'); ?>
                </td>
                <td class="facts_value">
                    <a href="#"
                       onclick="window.open('addmedia.php?action=showmediaform&amp;linktoid=<?php echo $controller->record->getXref(); ?>&amp;ged=<?php echo WT_GEDURL; ?>', '_blank', edit_window_specs); return false;">
                        <?php echo I18N::translate('Add a new media object'); ?>
                    </a>
                    <?php echo FunctionsPrint::i()->help_link('OBJE'); ?>
                    <br>
                    <a href="#"
                       onclick="window.open('inverselink.php?linktoid=<?php echo $controller->record->getXref(); ?>&amp;ged=<?php echo WT_GEDURL; ?>&amp;linkto=person', '_blank', find_window_specs); return false;">
                        <?php echo I18N::translate('Link to an existing media object'); ?>
                    </a>
                </td>
            </tr>
        <?php
        }
        ?>
		</table>
		<?php
        return '<div id="' . $this->getName() . '_content">' . ob_get_clean() . '</div>';
    }

    /**
     * Get all the facts for an individual which contain media objects.
     *
     * @return Fact[]
     */
    private function getFactsWithMedia()
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
                if (preg_match('/(?:^1|\n\d) OBJE @' . WT_REGEX_XREF . '@/', $fact->getGedcom())) {
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
