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
 * Class top10_givnnames_WT_Module
 */
class top10_givnnames_WT_Module extends Module implements ModuleBlockInterface
{
    /** {@inheritdoc} */
    public function getTitle()
    {
        return /* I18N: Name of a module.  Top=Most common */
            I18N::translate('Top given names');
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return /* I18N: Description of the “Top given names” module */
            I18N::translate('A list of the most popular given names.');
    }

    /** {@inheritdoc} */
    public function getBlock($block_id, $template = true, $cfg = null)
    {
        global $ctype;

        $num       = FunctionsDbPhp::i()->get_block_setting($block_id, 'num', '10');
        $infoStyle = FunctionsDbPhp::i()->get_block_setting($block_id, 'infoStyle', 'table');
        $block     = FunctionsDbPhp::i()->get_block_setting($block_id, 'block', '0');

        if ($cfg) {
            foreach (array(
                         'num',
                         'infoStyle',
                         'block'
                     ) as $name) {
                if (array_key_exists($name, $cfg)) {
                    $$name = $cfg[$name];
                }
            }
        }

        $stats = new Stats(Globals::i()->WT_TREE);

        $id    = $this->getName() . $block_id;
        $class = $this->getName() . '_block';
        if ($ctype === 'gedcom' && WT_USER_GEDCOM_ADMIN || $ctype === 'user' && Auth::check()) {
            $title = '<i class="icon-admin" title="' . I18N::translate('Configure') . '" onclick="modalDialog(\'block_edit.php?block_id=' . $block_id . '\', \'' . $this->getTitle() . '\');"></i>';
        } else {
            $title = '';
        }
        if ($num == 1) {
            // I18N: i.e. most popular given name.
            $title .= I18N::translate('Top given name');
        } else {
            // I18N: Title for a list of the most common given names, %s is a number.  Note that a separate translation exists when %s is 1
            $title .= I18N::plural('Top %s given name', 'Top %s given names', $num, I18N::number($num));
        }

        $content = '<div class="normal_inner_block">';
        //Select List or Table
        switch ($infoStyle) {
            case "list": // Output style 1:  Simple list style.  Better suited to left side of page.
                if (Globals::i()->TEXT_DIRECTION == 'ltr') {
                    $padding = 'padding-left: 15px';
                } else {
                    $padding = 'padding-right: 15px';
                }
                $params = array(
                    1,
                    $num,
                    'rcount'
                );
                //List Female names
                $totals = $stats->commonGivenFemaleTotals($params);
                if ($totals) {
                    $content .= '<b>' . I18N::translate('Females') . '</b><div class="wrap" style="' . $padding . '">' . $totals . '</div><br>';
                }
                //List Male names
                $totals = $stats->commonGivenMaleTotals($params);
                if ($totals) {
                    $content .= '<b>' . I18N::translate('Males') . '</b><div class="wrap" style="' . $padding . '">' . $totals . '</div><br>';
                }
                break;
            case "table": // Style 2: Tabular format.  Narrow, 2 or 3 column table, good on right side of page
                $params = array(
                    1,
                    $num,
                    'rcount'
                );
                $content .= '<table style="margin:auto;">
						<tr valign="top">
						<td>' . $stats->commonGivenFemaleTable($params) . '</td>
						<td>' . $stats->commonGivenMaleTable($params) . '</td>';
                $content .= '</tr></table>';
                break;
        }
        $content .= "</div>";

        if ($template) {
            if ($block) {
                $class .= ' small_inner_block';
            }

            return Application::i()->getTheme()
                        ->formatBlock($id, $title, $class, $content);
        } else {
            return $content;
        }
    }

    /** {@inheritdoc} */
    public function loadAjax()
    {
        return true;
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
            FunctionsDbPhp::i()->set_block_setting($block_id, 'num', Filter::postInteger('num', 1, 10000, 10));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'infoStyle', Filter::post('infoStyle', 'list|table', 'table'));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'block', Filter::postBool('block'));
        }

        $num       = FunctionsDbPhp::i()->get_block_setting($block_id, 'num', '10');
        $infoStyle = FunctionsDbPhp::i()->get_block_setting($block_id, 'infoStyle', 'table');
        $block     = FunctionsDbPhp::i()->get_block_setting($block_id, 'block', '0');

        echo '<tr><td class="descriptionbox wrap width33">';
        echo I18N::translate('Number of items to show');
        echo '</td><td class="optionbox">';
        echo '<input type="text" name="num" size="2" value="', $num, '">';
        echo '</td></tr>';

        echo '<tr><td class="descriptionbox wrap width33">';
        echo I18N::translate('Presentation style');
        echo '</td><td class="optionbox">';
        echo FunctionsEdit::i()->select_edit_control('infoStyle', array(
            'list'  => I18N::translate('list'),
            'table' => I18N::translate('table')
        ), null, $infoStyle, '');
        echo '</td></tr>';

        echo '<tr><td class="descriptionbox wrap width33">';
        echo /* I18N: label for a yes/no option */
        I18N::translate('Add a scrollbar when block contents grow');
        echo '</td><td class="optionbox">';
        echo FunctionsEdit::i()->edit_field_yes_no('block', $block);
        echo '</td></tr>';
    }
}
