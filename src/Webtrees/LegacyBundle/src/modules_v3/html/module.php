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
use Fgt\UrlConstants;

/**
 * Class html_WT_Module
 */
class html_WT_Module extends Module implements ModuleBlockInterface
{
    /** {@inheritdoc} */
    public function getTitle()
    {
        return /* I18N: Name of a module */
            I18N::translate('HTML');
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return /* I18N: Description of the “HTML” module */
            I18N::translate('Add your own text and graphics.');
    }

    /** {@inheritdoc} */
    public function getBlock($block_id, $template = true, $cfg = null)
    {
        global $ctype;

        $title          = FunctionsDbPhp::i()->get_block_setting($block_id, 'title');
        $html           = FunctionsDbPhp::i()->get_block_setting($block_id, 'html');
        $gedcom         = FunctionsDbPhp::i()->get_block_setting($block_id, 'gedcom');
        $show_timestamp = FunctionsDbPhp::i()->get_block_setting($block_id, 'show_timestamp', '0');
        $languages      = FunctionsDbPhp::i()->get_block_setting($block_id, 'languages');

        // Only show this block for certain languages
        if ($languages && !in_array(WT_LOCALE, explode(',', $languages))) {
            return '';
        }

        /*
         * Select GEDCOM
         */
        switch ($gedcom) {
            case '__current__':
                $stats = new Stats(Globals::i()->WT_TREE);
                break;
            case '__default__':
                $tree_id = Tree::getIdFromName(Site::getPreference('DEFAULT_GEDCOM'));
                if ($tree_id) {
                    $stats = new Stats(Tree::get($tree_id));
                } else {
                    $stats = new Stats(Globals::i()->WT_TREE);
                }
                break;
            default:
                $tree_id = Tree::getIdFromName($gedcom);
                if ($tree_id) {
                    $stats = new Stats(Tree::get($tree_id));
                } else {
                    $stats = new Stats(Globals::i()->WT_TREE);
                }
                break;
        }

        /*
        * Retrieve text, process embedded variables
        */
        if (strpos($title, '#') !== false || strpos($html, '#') !== false) {
            $title = $stats->embedTags($title);
            $html  = $stats->embedTags($html);
        }

        /*
        * Start Of Output
        */
        $id    = $this->getName() . $block_id;
        $class = $this->getName() . '_block';
        if ($ctype === 'gedcom' && WT_USER_GEDCOM_ADMIN || $ctype === 'user' && Auth::check()) {
            $title = '<i class="icon-admin" title="' . I18N::translate('Configure') . '" onclick="modalDialog(\'block_edit.php?block_id=' . $block_id . '\', \'' . $this->getTitle() . '\');"></i>' . $title;
        }

        $content = $html;

        if ($show_timestamp) {
            $content .= '<br>' . FunctionsDate::i()->format_timestamp(FunctionsDbPhp::i()
                                                                                    ->get_block_setting($block_id, 'timestamp', WT_TIMESTAMP));
        }

        if ($template) {
            return Application::i()->getTheme()
                        ->formatBlock($id, $title, $class, $content);
        } else {
            return $content;
        }
    }

    /** {@inheritdoc} */
    public function loadAjax()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function isUserBlock()
    {
        return true;
    }

    /** {@inheritdoc} */
    public function isGedcomBlock()
    {
        return true;
    }

    /** {@inheritdoc} */
    public function configureBlock($block_id)
    {
        if (Filter::postBool('save') && Filter::checkCsrf()) {
            FunctionsDbPhp::i()->set_block_setting($block_id, 'gedcom', Filter::post('gedcom'));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'title', Filter::post('title'));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'html', Filter::post('html'));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'show_timestamp', Filter::postBool('show_timestamp'));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'timestamp', Filter::post('timestamp'));
            $languages = Filter::postArray('lang', null, array_keys(I18N::installed_languages()));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'languages', implode(',', $languages));
        }

        $templates = array(
            I18N::translate('Keyword examples')      =>
                '#getAllTagsTable#',

            I18N::translate('Narrative description') =>
            /* I18N: do not translate the #keywords# */
                I18N::translate('This family tree was last updated on #gedcomUpdated#.  There are #totalSurnames# surnames in this family tree.  The earliest recorded event is the #firstEventType# of #firstEventName# in #firstEventYear#.  The most recent event is the #lastEventType# of #lastEventName# in #lastEventYear#.<br><br>If you have any comments or feedback please contact #contactWebmaster#.'),

            I18N::translate('Statistics')            =>
                '<div class="gedcom_stats">
				<span style="font-weight: bold;"><a href="' . UrlConstants::url(UrlConstants::INDEX_PHP, ['command' => 'gedcom']) . '">#gedcomTitle#</a></span><br>
				' . I18N::translate('This family tree was last updated on %s.', '#gedcomUpdated#') . '
				<table id="keywords">
					<tr>
						<td valign="top" class="width20">
							<table cellspacing="1" cellpadding="0">
								<tr>
									<td class="facts_label">' . I18N::translate('Individuals') . '</td>
									<td class="facts_value" align="right"><a href="indilist.php?surname_sublist=no">#totalIndividuals#</a></td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Males') . '</td>
									<td class="facts_value" align="right">#totalSexMales#<br>#totalSexMalesPercentage#</td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Females') . '</td>
									<td class="facts_value" align="right">#totalSexFemales#<br>#totalSexFemalesPercentage#</td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Total surnames') . '</td>
									<td class="facts_value" align="right"><a href="indilist.php?show_all=yes&amp;surname_sublist=yes&amp;ged=' . WT_GEDURL . '">#totalSurnames#</a></td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Families') . '</td>
									<td class="facts_value" align="right"><a href="famlist.php?ged=' . WT_GEDURL . '">#totalFamilies#</a></td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Sources') . '</td>
									<td class="facts_value" align="right"><a href="sourcelist.php?ged=' . WT_GEDURL . '">#totalSources#</a></td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Media objects') . '</td>
									<td class="facts_value" align="right"><a href="medialist.php?ged=' . WT_GEDURL . '">#totalMedia#</a></td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Repositories') . '</td>
									<td class="facts_value" align="right"><a href="repolist.php?ged=' . WT_GEDURL . '">#totalRepositories#</a></td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Total events') . '</td>
									<td class="facts_value" align="right">#totalEvents#</td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Total users') . '</td>
									<td class="facts_value" align="right">#totalUsers#</td>
								</tr>
							</table>
						</td>
						<td><br></td>
						<td valign="top">
							<table cellspacing="1" cellpadding="0" border="0">
								<tr>
									<td class="facts_label">' . I18N::translate('Earliest birth year') . '</td>
									<td class="facts_value" align="right">#firstBirthYear#</td>
									<td class="facts_value">#firstBirth#</td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Latest birth year') . '</td>
									<td class="facts_value" align="right">#lastBirthYear#</td>
									<td class="facts_value">#lastBirth#</td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Earliest death year') . '</td>
									<td class="facts_value" align="right">#firstDeathYear#</td>
									<td class="facts_value">#firstDeath#</td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Latest death year') . '</td>
									<td class="facts_value" align="right">#lastDeathYear#</td>
									<td class="facts_value">#lastDeath#</td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Individual who lived the longest') . '</td>
									<td class="facts_value" align="right">#longestLifeAge#</td>
									<td class="facts_value">#longestLife#</td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Average age at death') . '</td>
									<td class="facts_value" align="right">#averageLifespan#</td>
									<td class="facts_value"></td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Family with the most children') . '</td>
									<td class="facts_value" align="right">#largestFamilySize#</td>
									<td class="facts_value">#largestFamily#</td>
								</tr>
								<tr>
									<td class="facts_label">' . I18N::translate('Average number of children per family') . '</td>
									<td class="facts_value" align="right">#averageChildren#</td>
									<td class="facts_value"></td>
								</tr>
							</table>
						</td>
					</tr>
				</table><br>
				<span style="font-weight: bold;">' . I18N::translate('Most common surnames') . '</span><br>
				#commonSurnames#
			</div>'
        );

        $title          = FunctionsDbPhp::i()->get_block_setting($block_id, 'title');
        $html           = FunctionsDbPhp::i()->get_block_setting($block_id, 'html');
        $gedcom         = FunctionsDbPhp::i()->get_block_setting($block_id, 'gedcom');
        $show_timestamp = FunctionsDbPhp::i()->get_block_setting($block_id, 'show_timestamp', '0');
        $languages      = explode(',', FunctionsDbPhp::i()->get_block_setting($block_id, 'languages'));

        echo '<tr><td class="descriptionbox wrap">',
        WT_Gedcom_Tag::getLabel('TITL'),
        '</td><td class="optionbox"><input type="text" name="title" size="30" value="', Filter::escapeHtml($title), '"></td></tr>';

        // templates
        echo '<tr><td class="descriptionbox wrap">',
        I18N::translate('Templates'),
        '</td><td class="optionbox wrap">';
        // The CK editor needs lots of help to load/save data :-(
        if (array_key_exists('ckeditor', Module::getActiveModules())) {
            $ckeditor_onchange = 'CKEDITOR.instances.html.setData(document.block.html.value);';
        } else {
            $ckeditor_onchange = '';
        }
        echo '<select name="template" onchange="document.block.html.value=document.block.template.options[document.block.template.selectedIndex].value;', $ckeditor_onchange, '">';
        echo '<option value="', Filter::escapeHtml($html), '">', I18N::translate('Custom'), '</option>';
        foreach ($templates as $title => $template) {
            echo '<option value="', Filter::escapeHtml($template), '">', $title, '</option>';
        }
        echo '</select>';
        if (!$html) {
            echo '<p>', I18N::translate('To assist you in getting started with this block, we have created several standard templates.  When you select one of these templates, the text area will contain a copy that you can then alter to suit your site’s requirements.'), '</p>';
        }
        echo '</td></tr>';

        if (count(Tree::getAll()) > 1) {
            if ($gedcom == '__current__') {
                $sel_current = 'selected';
            } else {
                $sel_current = '';
            }
            if ($gedcom == '__default__') {
                $sel_default = 'selected';
            } else {
                $sel_default = '';
            }
            echo '<tr><td class="descriptionbox wrap">',
            I18N::translate('Family tree'),
            '</td><td class="optionbox">',
            '<select name="gedcom">',
            '<option value="__current__" ', $sel_current, '>', I18N::translate('Current'), '</option>',
            '<option value="__default__" ', $sel_default, '>', I18N::translate('Default'), '</option>';
            foreach (Tree::getAll() as $tree) {
                if ($tree->getName() === $gedcom) {
                    $sel = 'selected';
                } else {
                    $sel = '';
                }
                echo '<option value="', $tree->getNameHtml(), '" ', $sel, ' dir="auto">', $tree->getTitleHtml(), '</option>';
            }
            echo '</select>';
            echo '</td></tr>';
        }

        // html
        echo '<tr><td colspan="2" class="descriptionbox">',
        I18N::translate('Content');
        if (!$html) {
            echo '<p>', I18N::translate('As well as using the toolbar to apply HTML formatting, you can insert database fields which are updated automatically.  These special fields are marked with <b>#</b> characters.  For example <b>#totalFamilies#</b> will be replaced with the actual number of families in the database.  Advanced users may wish to apply CSS classes to their text, so that the formatting matches the currently selected theme.'), '</p>';
        }
        echo
        '</td></tr><tr>',
        '<td colspan="2" class="optionbox">';
        echo '<textarea name="html" class="html-edit" rows="10" style="width:98%;">', Filter::escapeHtml($html), '</textarea>';
        echo '</td></tr>';

        echo '<tr><td class="descriptionbox wrap">';
        echo I18N::translate('Show the date and time of update');
        echo '</td><td class="optionbox">';
        echo FunctionsEdit::i()->edit_field_yes_no('show_timestamp', $show_timestamp);
        echo '<input type="hidden" name="timestamp" value="', WT_TIMESTAMP, '">';
        echo '</td></tr>';

        echo '<tr><td class="descriptionbox wrap">';
        echo I18N::translate('Show this block for which languages?');
        echo '</td><td class="optionbox">';
        echo FunctionsEdit::i()->edit_language_checkboxes('lang', $languages);
        echo '</td></tr>';
    }
}
