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

/**
 * Defined in \Fgt\Application
 *
 * @global integer $bwidth
 */
global $bwidth;

use Fgt\Application;
use Fgt\Globals;

define('WT_SCRIPT_NAME', 'relationship.php');
Application::i()->init()->started();

$controller = Application::i()->setActiveController(new PageController());

$pid1         = Filter::get('pid1', WT_REGEX_XREF);
$pid2         = Filter::get('pid2', WT_REGEX_XREF);
$show_full    = Filter::getInteger('show_full', 0, 1, Globals::i()->WT_TREE->getPreference('PEDIGREE_FULL_DETAILS'));
$path_to_find = Filter::getInteger('path_to_find');
$followspouse = Filter::getBool('followspouse');
$asc          = Filter::getBool('asc');

$asc     = $asc ? -1 : 1;
$Dbwidth = $bwidth;
if (!$show_full) {
    $bwidth  = Application::i()->getTheme()
                    ->parameter('compact-chart-box-x');
    $bheight = Application::i()->getTheme()
                    ->parameter('compact-chart-box-y');
    $Dbwidth = Application::i()->getTheme()
                    ->parameter('compact-chart-box-x');
}

$Dbheight     = $bheight;
$Dbxspacing   = 0;
$Dbyspacing   = 0;
$Dbasexoffset = 0;
$Dbaseyoffset = 0;

$person1 = Individual::getInstance($pid1);
$person2 = Individual::getInstance($pid2);

$controller
    ->addExternalJavascript(WT_STATIC_URL.WebtreesTheme::WT_AUTOCOMPLETE_JS_URL)
    ->addInlineJavascript('autocomplete();');

if ($person1 && $person1->canShowName() && $person2 && $person2->canShowName()) {
    $controller
        ->setPageTitle(I18N::translate(/* I18N: %s are individual’s names */
            'Relationships between %1$s and %2$s', $person1->getFullName(), $person2->getFullName()))
        ->PageHeader();
    $node = Functions::i()->get_relationship($person1, $person2, $followspouse, 0, $path_to_find);
    // If no blood relationship exists, look for relationship via marriage
    if ($path_to_find == 0 && $node == false && $followspouse == false) {
        $followspouse = true;
        $node         = Functions::i()->get_relationship($person1, $person2, $followspouse, 0, $path_to_find);
    }
    $disp = true;
} else {
    $controller
        ->setPageTitle(I18N::translate('Relationships'))
        ->PageHeader();
    $node = false;
    $disp = false;
}

?>
    <div id="relationship-page">
    <h2><?php echo $controller->getPageTitle(); ?></h2>

    <form name="people" method="get" action="?">
        <input type="hidden" name="ged" value="<?php echo Filter::escapeHtml(WT_GEDCOM); ?>">
        <input type="hidden" name="path_to_find" value="0">
        <table class="list_table">
            <tr>
                <td colspan="2" class="topbottombar center">
                    <?php echo I18N::translate('Relationships'); ?>
                </td>
                <td colspan="2" class="topbottombar center">
                    <?php echo I18N::translate('Options:'); ?>
                </td>
            </tr>
            <tr>
                <td class="descriptionbox">
                    <?php echo I18N::translate('Individual 1'); ?>
                </td>
                <td class="optionbox vmiddle">
                    <input tabindex="1" class="pedigree_form" data-autocomplete-type="INDI" type="text" name="pid1"
                           id="pid1" size="3" value="<?php echo $pid1; ?>">
                    <?php echo FunctionsPrint::i()->print_findindi_link('pid1'); ?>
                </td>
                <td class="descriptionbox">
                    <?php echo I18N::translate('Show details'); ?>
                </td>
                <td class="optionbox vmiddle">
                    <?php echo FunctionsEdit::i()->two_state_checkbox('show_full', $show_full); ?>
                </td>
            </tr>
            <tr>
                <td class="descriptionbox">
                    <?php echo I18N::translate('Individual 2'); ?>
                </td>
                <td class="optionbox vmiddle">
                    <input tabindex="2" class="pedigree_form" data-autocomplete-type="INDI" type="text" name="pid2"
                           id="pid2" size="3" value="<?php echo $pid2; ?>">
                    <?php echo FunctionsPrint::i()->print_findindi_link('pid2'); ?>
                </td>
                <td class="descriptionbox">
                    <?php echo I18N::translate('Show oldest top'); ?>
                </td>
                <td class="optionbox">
                    <input tabindex="4" type="checkbox" name="asc" value="1" <?php echo $asc === -1 ? 'checked'
                        : ''; ?>>
                </td>
            </tr>
            <tr>
                <td class="descriptionbox">
                    <?php
                    if ($path_to_find > 0) {
                        echo I18N::translate('Show path');
                    }
                    ?>
                </td>
                <td class="optionbox">
                    <?php
                    for ($i = 0; $i < $path_to_find; ++$i) {
                        echo ' <a href="relationship.php?pid1=', $pid1, '&amp;pid2=', $pid2, '&amp;path_to_find=', $i, '&amp;followspouse=', $followspouse, '&amp;show_full=', $show_full, '&amp;asc=', -$asc, '">', $i + 1, '</a>';
                    }
                    ?>
                </td>
                <td class="descriptionbox">
                    <?php echo I18N::translate('Check relationships by marriage'), FunctionsPrint::i()->help_link('CHECK_MARRIAGE_RELATIONS'); ?>
                </td>
                <td class="optionbox" id="followspousebox">
                    <input tabindex="6" type="checkbox" name="followspouse" value="1" <?php echo $followspouse
                        ? 'checked' : ''; ?> onclick="document.people.path_to_find.value='-1';">
                </td>
            </tr>
            <td class="topbottombar vmiddle center" colspan="2">
                <?php
                if ($node) {
                    echo '<input type="submit" value="', I18N::translate('Find next path'), '" onclick="document.people.path_to_find.value=', $path_to_find + 1, ';">';
                    echo FunctionsPrint::i()->help_link('next_path');
                }
                ?>
            </td>
            <td class="topbottombar vmiddle center" colspan="2">
                <input tabindex="7" type="submit" value="<?php echo I18N::translate('View'); ?>">
            </td>
            </tr>
        </table>
    </form>

<?php

$maxyoffset = $Dbaseyoffset;
if ($person1 && $person2) {
    if (!$disp) {
        echo '<div class="error">', I18N::translate('This information is private and cannot be shown.'), '</div>';
    } elseif (!$node) {
        if ($path_to_find == 0) {
            echo '<p class="error">', I18N::translate('No link between the two individuals could be found.'), '</p>';
        } else {
            echo '<p class="error">', I18N::translate('No other link between the two individuals could be found.'), '</p>';
        }
    } else {
        if ($node) {
            echo '<h3>', I18N::translate('Relationship: %s', Functions::i()->get_relationship_name($node)), '</h3>';

            // Use relative layout to position the person boxes.
            echo '<div id="relationship_chart" style="position:relative;">';

            $yoffset      = $Dbaseyoffset + 20;
            $xoffset      = $Dbasexoffset;
            $colNum       = 0;
            $rowNum       = 0;
            $previous     = '';
            $change_count = ''; // shift right on alternate change of direction
            $xs           = $Dbxspacing + 70;
            $ys           = $Dbyspacing + 50;
            // step1 = tree depth calculation
            $dmin  = 0;
            $dmax  = 0;
            $depth = 0;
            foreach ($node['path'] as $index => $person) {
                if ($node['relations'][$index] == 'father' || $node['relations'][$index] == 'mother' || $node['relations'][$index] == 'parent') {
                    $depth++;
                    if ($depth > $dmax) {
                        $dmax = $depth;
                    }
                    if ($asc == 0) {
                        $asc = 1; // the first link is a parent link
                    }
                }
                if ($node['relations'][$index] == 'son' || $node['relations'][$index] == 'daughter' || $node['relations'][$index] == 'child') {
                    $depth--;
                    if ($depth < $dmin) {
                        $dmin = $depth;
                    }
                    if ($asc == 0) {
                        $asc = -1; // the first link is a child link
                    }
                }
            }
            $depth = $dmax + $dmin;
            // need more yoffset before the first box ?
            if ($asc == 1) {
                $yoffset -= $dmin * ($Dbheight + $ys);
            }
            if ($asc == -1) {
                $yoffset += $dmax * ($Dbheight + $ys);
            }
            $rowNum     = ($asc == -1) ? $depth : 0;
            $maxxoffset = -1 * $Dbwidth - 20;
            $maxyoffset = $yoffset;
            // Left and right get reversed on RTL pages
            if (Globals::i()->TEXT_DIRECTION == 'ltr') {
                $right_arrow = 'icon-rarrow';
            } else {
                $right_arrow = 'icon-larrow';
            }
            // Up and down get reversed, for the “oldest at top” option
            if ($asc == 1) {
                $up_arrow   = 'icon-uarrow';
                $down_arrow = 'icon-darrow';
            } else {
                $up_arrow   = 'icon-darrow';
                $down_arrow = 'icon-uarrow';
            }
            foreach ($node['path'] as $index => $person) {
                $linex = $xoffset;
                $liney = $yoffset;
                switch ($person->getSex()) {
                    case 'M':
                        $mfstyle = '';
                        break;
                    case 'F':
                        $mfstyle = 'F';
                        break;
                    default:
                        $mfstyle = 'NN';
                        break;
                }
                switch ($node['relations'][$index]) {
                    case 'father':
                    case 'mother':
                    case 'parent':
                        $arrow_img = $down_arrow;
                        $line      = Application::i()->getTheme()
                                          ->parameter('image-vline');
                        $liney += $Dbheight;
                        $linex += $Dbwidth / 2;
                        $lh    = 54;
                        $lw    = 3;
                        $lh    = $ys;
                        $linex = $xoffset + $Dbwidth / 2;
                        // put the box up or down ?
                        $yoffset += $asc * ($Dbheight + $lh);
                        $rowNum += $asc;
                        if ($asc == 1) {
                            $liney = $yoffset - $lh;
                        } else {
                            $liney = $yoffset + $Dbheight;
                        }
                        // need to draw a joining line ?
                        if ($previous == 'child' && ($change_count++ % 2) == 0) {
                            $joinh = 3;
                            $joinw = $xs / 2 + 2;
                            $xoffset += $Dbwidth + $xs;
                            $colNum++;
                            //$rowNum is inherited from the box immediately to the left
                            $linex = $xoffset - $xs / 2;
                            if ($asc == -1) {
                                $liney = $yoffset + $Dbheight;
                            } else {
                                $liney = $yoffset - $lh;
                            }
                            $joinx = $xoffset - $xs;
                            $joiny = $liney - 2 - ($asc - 1) / 2 * $lh;
                            echo "<div id=\"joina", $index, "\" style=\"position:absolute; ", Globals::i()->TEXT_DIRECTION == 'ltr'
                                ? 'left'
                                : 'right', ':', $joinx + $Dbxspacing, 'px; top:', $joiny + $Dbyspacing, "px;\" align=\"center\"><img src=\"", Application::i()->getTheme()
                                                                                                                                                   ->parameter('image-hline'), "\" align=\"left\" width=\"", $joinw, "\" height=\"", $joinh, "\" alt=\"\"></div>";
                            $joinw = $xs / 2 + 2;
                            $joinx = $joinx + $xs / 2;
                            $joiny = $joiny + $asc * $lh;
                            echo "<div id=\"joinb", $index, "\" style=\"position:absolute; ", Globals::i()->TEXT_DIRECTION == 'ltr'
                                ? 'left'
                                : 'right', ':', $joinx + $Dbxspacing, 'px; top:', $joiny + $Dbyspacing, "px;\" align=\"center\"><img src=\"", Application::i()->getTheme()
                                                                                                                                                   ->parameter('image-hline'), "\" align=\"left\" width=\"", $joinw, "\" height=\"", $joinh, "\" alt=\"\"></div>";
                        } else {
                            $change_count = '';
                        }
                        $previous = 'parent';
                        break;
                    case 'brother':
                    case 'sister':
                    case 'sibling':
                    case 'husband':
                    case 'wife':
                    case 'spouse':
                        $arrow_img = $right_arrow;
                        $xoffset += $Dbwidth + $Dbxspacing + 70;
                        $colNum++;
                        //$rowNum is inherited from the box immediately to the left
                        $line = Application::i()->getTheme()
                                     ->parameter('image-hline');
                        $linex += $Dbwidth;
                        $liney += $Dbheight / 2;
                        $lh       = 3;
                        $lw       = 70;
                        $lw       = $xs;
                        $linex    = $xoffset - $lw;
                        $liney    = $yoffset + $Dbheight / 4;
                        $previous = '';
                        break;
                    case 'son':
                    case 'daughter':
                    case 'child':
                        $arrow_img = $up_arrow;
                        $line      = Application::i()->getTheme()
                                          ->parameter('image-vline');
                        $liney += $Dbheight;
                        $linex += $Dbwidth / 2;
                        $lh    = 54;
                        $lw    = 3;
                        $lh    = $ys;
                        $linex = $xoffset + $Dbwidth / 2;
                        // put the box up or down ?
                        $yoffset -= $asc * ($Dbheight + $lh);
                        $rowNum -= $asc;
                        if ($asc == -1) {
                            $liney = $yoffset - $lh;
                        } else {
                            $liney = $yoffset + $Dbheight;
                        }
                        // need to draw a joining line ?
                        if ($previous == 'parent' && ($change_count++ % 2) == 0) {
                            $joinh = 3;
                            $joinw = $xs / 2 + 2;
                            $xoffset += $Dbwidth + $xs;
                            $colNum++;
                            //$rowNum is inherited from the box immediately to the left
                            $linex = $xoffset - $xs / 2;
                            if ($asc == 1) {
                                $liney = $yoffset + $Dbheight;
                            } else {
                                $liney = $yoffset - ($lh + $Dbyspacing);
                            }
                            $joinx = $xoffset - $xs;
                            $joiny = $liney - 2 + ($asc + 1) / 2 * $lh;
                            echo '<div id="joina', $index, '" style="position:absolute; ', Globals::i()->TEXT_DIRECTION == 'ltr'
                                ? 'left'
                                : 'right', ':', $joinx + $Dbxspacing, 'px; top:', $joiny + $Dbyspacing, 'px;" align="center"><img src="', Application::i()->getTheme()
                                                                                                                                               ->parameter('image-hline'), '" align="left" width="', $joinw, '" height="', $joinh, '" alt=""></div>';
                            $joinw = $xs / 2 + 2;
                            $joinx = $joinx + $xs / 2;
                            $joiny = $joiny - $asc * $lh;
                            echo '<div id="joinb', $index, '" style="position:absolute; ', Globals::i()->TEXT_DIRECTION == 'ltr'
                                ? 'left'
                                : 'right', ':', $joinx + $Dbxspacing, 'px; top:', $joiny + $Dbyspacing, 'px;" align="center"><img src="', Application::i()->getTheme()
                                                                                                                                               ->parameter('image-hline'), '" align="left" width="', $joinw, '" height="', $joinh, '" alt=""></div>';
                        } else {
                            $change_count = '';
                        }
                        $previous = 'child';
                        break;
                }
                if ($yoffset > $maxyoffset) {
                    $maxyoffset = $yoffset;
                }
                $plinex   = $linex;
                $pxoffset = $xoffset;

                // Adjust all box positions for proper placement with respect to other page elements
                $pyoffset = $yoffset - 2;

                if ($index > 0) {
                    if (Globals::i()->TEXT_DIRECTION === 'rtl'
                        && $line !== Application::i()->getTheme()
                                          ->parameter('image-hline')
                    ) {
                        echo '<div id="line', $index, '" style="background:none; position:absolute; right:', $plinex + $Dbxspacing, 'px; top:', $liney + $Dbyspacing, 'px; width:', $lw + $lh * 2, 'px;" align="right">';
                        echo '<img src="', $line, '" align="right" width="', $lw, '" height="', $lh, '" alt="">';
                        echo '<br>';
                        echo I18N::translate($node['relations'][$index]);
                        echo '<i class="', $arrow_img, '"></i>';
                    } else {
                        echo '<div id="line', $index, '" style="background:none; position:absolute; ', Globals::i()->TEXT_DIRECTION == 'ltr'
                            ? 'left'
                            : 'right', ':', $plinex + $Dbxspacing, 'px; top:', $liney + $Dbyspacing, 'px; width:', $lw + $lh * 2, 'px;" align="', $lh == 3
                            ? 'center'
                            : 'left', '"><img src="', $line, '" align="left" width="', $lw, '" height="', $lh, '" alt="">';
                        echo '<br>';
                        echo '<i class="', $arrow_img, '"></i>';
                        if ($lh == 3) {
                            echo '<br>'; // note: $lh==3 means horiz arrow
                        }
                        echo I18N::translate($node['relations'][$index]);
                    }
                    echo '</div>';
                }

                // Determine the z-index for this box
                $zIndex = 200 - ($colNum * $depth + $rowNum);

                echo '<div style="position:absolute; ', Globals::i()->TEXT_DIRECTION == 'ltr' ? 'left'
                    : 'right', ':', $pxoffset, 'px; top:', $pyoffset, 'px; width:', $Dbwidth, 'px; height:', $Dbheight, 'px; z-index:', $zIndex, ';">';
                FunctionsPrint::i()->print_pedigree_person($person);
                echo '</div>';
            }
        }
        echo '</div>'; // close#relationship_chart
    }
}
echo '</div>'; // close #relationshippage

// The contents of <div id="relationship_chart"> use relative positions.
// Need to expand the div to include the children, or we'll overlap the footer.
// $maxyoffset is the top edge of the lowest box.
$controller->addInlineJavascript('
	relationship_chart_div = document.getElementById("relationship_chart");
	if (relationship_chart_div) {
		relationship_chart_div.style.height = "' . ($maxyoffset + $Dbheight + 20) . 'px";
		relationship_chart_div.style.width = "100%";
	}'
);
