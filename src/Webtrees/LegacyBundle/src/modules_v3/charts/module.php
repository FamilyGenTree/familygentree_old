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
 * Class charts_WT_Module
 */
class charts_WT_Module extends Module implements ModuleBlockInterface
{
    /** {@inheritdoc} */
    public function getTitle()
    {
        return /* I18N: Name of a module/block */
            I18N::translate('Charts');
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return /* I18N: Description of the “Charts” module */
            I18N::translate('An alternative way to display charts.');
    }

    /** {@inheritdoc} */
    public function getBlock($block_id, $template = true, $cfg = null)
    {
        global $ctype, $show_full;
        $controller = Application::i()->getActiveController();

        $PEDIGREE_ROOT_ID = Globals::i()->WT_TREE->getPreference('PEDIGREE_ROOT_ID');

        $details = FunctionsDbPhp::i()->get_block_setting($block_id, 'details', '0');
        $type    = FunctionsDbPhp::i()->get_block_setting($block_id, 'type', 'pedigree');
        $pid     = FunctionsDbPhp::i()->get_block_setting($block_id, 'pid', Auth::check() ? (WT_USER_GEDCOM_ID ? WT_USER_GEDCOM_ID
            : $PEDIGREE_ROOT_ID) : $PEDIGREE_ROOT_ID);

        if ($cfg) {
            foreach (array(
                         'details',
                         'type',
                         'pid',
                         'block'
                     ) as $name) {
                if (array_key_exists($name, $cfg)) {
                    $$name = $cfg[$name];
                }
            }
        }

        if (!$details) {
            $show_full = 0;
            // Here we could adjust the block width & height to accommodate larger displays
        } else {
            $show_full = 1;
            // Here we could adjust the block width & height to accommodate larger displays
        }

        $person = Individual::getInstance($pid);
        if (!$person) {
            $pid = $PEDIGREE_ROOT_ID;
            FunctionsDbPhp::i()->set_block_setting($block_id, 'pid', $pid);
            $person = Individual::getInstance($pid);
        }

        if ($type != 'treenav' && $person) {
            $chartController = new HourglassController($person->getXref(), 0, false);
            $controller->addInlineJavascript($chartController->setupJavascript());
        }

        $id    = $this->getName() . $block_id;
        $class = $this->getName() . '_block';
        if ($ctype == 'gedcom' && WT_USER_GEDCOM_ADMIN || $ctype == 'user' && Auth::check()) {
            $title = '<i class="icon-admin" title="' . I18N::translate('Configure') . '" onclick="modalDialog(\'block_edit.php?block_id=' . $block_id . '\', \'' . $this->getTitle() . '\');"></i>';
        } else {
            $title = '';
        }

        if ($person) {
            switch ($type) {
                case 'pedigree':
                    $title .= I18N::translate('Pedigree of %s', $person->getFullName());
                    break;
                case 'descendants':
                    $title .= I18N::translate('Descendants of %s', $person->getFullName());
                    break;
                case 'hourglass':
                    $title .= I18N::translate('Hourglass chart of %s', $person->getFullName());
                    break;
                case 'treenav':
                    $title .= I18N::translate('Interactive tree of %s', $person->getFullName());
                    break;
            }
            $title .= FunctionsPrint::i()->help_link('index_charts', $this->getName());
            $content = '<table cellspacing="0" cellpadding="0" border="0"><tr>';
            if ($type == 'descendants' || $type == 'hourglass') {
                $content .= "<td valign=\"middle\">";
                ob_start();
                $chartController->printDescendency($person, 1, false);
                $content .= ob_get_clean();
                $content .= "</td>";
            }
            if ($type == 'pedigree' || $type == 'hourglass') {
                //-- print out the root person
                if ($type != 'hourglass') {
                    $content .= "<td valign=\"middle\">";
                    ob_start();
                    FunctionsPrint::i()->print_pedigree_person($person);
                    $content .= ob_get_clean();
                    $content .= "</td>";
                }
                $content .= "<td valign=\"middle\">";
                ob_start();
                $chartController->printPersonPedigree($person, 1);
                $content .= ob_get_clean();
                $content .= "</td>";
            }
            if ($type == 'treenav') {
                require_once WT_MODULES_DIR . 'tree/module.php';
                require_once WT_MODULES_DIR . 'tree/class_treeview.php';
                $mod = new tree_WT_Module;
                $tv  = new TreeView;
                $content .= '<td>';

                $content .= '<script>jQuery("head").append(\'<link rel="stylesheet" href="' . $mod->css() . '" type="text/css" />\');</script>';
                $content .= '<script src="' . $mod->js() . '"></script>';
                list($html, $js) = $tv->drawViewport($person, 2);
                $content .= $html . '<script>' . $js . '</script>';
                $content .= '</td>';
            }
            $content .= "</tr></table>";
        } else {
            $content = I18N::translate('You must select an individual and chart type in the block configuration settings.');
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
        $controller = Application::i()->getActiveController();

        $PEDIGREE_ROOT_ID = Globals::i()->WT_TREE->getPreference('PEDIGREE_ROOT_ID');

        if (Filter::postBool('save') && Filter::checkCsrf()) {
            FunctionsDbPhp::i()->set_block_setting($block_id, 'details', Filter::postBool('details'));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'type', Filter::post('type', 'pedigree|descendants|hourglass|treenav', 'pedigree'));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'pid', Filter::post('pid', WT_REGEX_XREF));
        }

        $details = FunctionsDbPhp::i()->get_block_setting($block_id, 'details', '0');
        $type    = FunctionsDbPhp::i()->get_block_setting($block_id, 'type', 'pedigree');
        $pid     = FunctionsDbPhp::i()->get_block_setting($block_id, 'pid', Auth::check() ? (WT_USER_GEDCOM_ID ? WT_USER_GEDCOM_ID
            : $PEDIGREE_ROOT_ID) : $PEDIGREE_ROOT_ID);

        $controller
            ->addExternalJavascript(WT_STATIC_URL.WebtreesTheme::WT_AUTOCOMPLETE_JS_URL)
            ->addInlineJavascript('autocomplete();');
        ?>
        <tr>
            <td colspan="2">
                <?php echo I18N::translate('This block allows a pedigree, descendancy, or hourglass chart to appear on your “My page” or the “Home page”.  Because of space limitations, the charts should be placed only on the left side of the page.<br><br>When this block appears on the “Home page”, the root individual and the type of chart to be displayed are determined by the administrator.  When this block appears on the user’s “My page”, these options are determined by the user.<br><br>The behavior of these charts is identical to their behavior when they are called up from the menus.  Click on the box of an individual to see more details about them.'); ?>
            </td>
        </tr>
        <tr>
            <td class="descriptionbox wrap width33"><?php echo I18N::translate('Chart type'); ?></td>
            <td class="optionbox">
                <?php echo FunctionsEdit::i()->select_edit_control('type',
                                               array(
                                                   'pedigree'    => I18N::translate('Pedigree'),
                                                   'descendants' => I18N::translate('Descendants'),
                                                   'hourglass'   => I18N::translate('Hourglass chart'),
                                                   'treenav'     => I18N::translate('Interactive tree')
                                               ),
                                               null, $type); ?>
            </td>
        </tr>
        <tr>
            <td class="descriptionbox wrap width33"><?php echo I18N::translate('Show details'); ?></td>
            <td class="optionbox">
                <?php echo FunctionsEdit::i()->edit_field_yes_no('details', $details); ?>
            </td>
        </tr>
        <tr>
            <td class="descriptionbox wrap width33"><?php echo I18N::translate('Individual'); ?></td>
            <td class="optionbox">
                <input data-autocomplete-type="INDI" type="text" name="pid" id="pid" value="<?php echo $pid; ?>"
                       size="5">
                <?php
                echo FunctionsPrint::i()->print_findindi_link('pid');
                $root = Individual::getInstance($pid);
                if ($root) {
                    echo ' <span class="list_item">', $root->getFullName(), $root->format_first_major_fact(WT_EVENTS_BIRT, 1), '</span>';
                }
                ?>
            </td>
        </tr>
    <?php
    }
}
