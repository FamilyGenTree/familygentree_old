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

class FunctionsCharts
{
    /**
     * @var FunctionsCharts
     */
    protected static $instance;

    /**
     * Singleton protected
     */
    protected function __construct()
    {

    }

    /**
     * @return FunctionsCharts
     */
    public static function i()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
    /**
     * print a table cell with sosa number
     *
     * @param integer $sosa
     * @param string  $pid            optional pid
     * @param string  $arrowDirection direction of link arrow
     */
    function print_sosa_number($sosa, $pid = "", $arrowDirection = "up")
    {
        if (substr($sosa, -1, 1) == ".") {
            $personLabel = substr($sosa, 0, -1);
        } else {
            $personLabel = $sosa;
        }
        if ($arrowDirection == "blank") {
            $visibility = "hidden";
        } else {
            $visibility = "normal";
        }
        echo "<td class=\"subheaders center\" style=\"vertical-align: middle; text-indent: 0px; margin-top: 0px; white-space: nowrap; visibility: ", $visibility, ";\">";
        echo $personLabel;
        if ($sosa != "1" && $pid != "") {
            if ($arrowDirection == "left") {
                $dir = 0;
            } elseif ($arrowDirection == "right") {
                $dir = 1;
            } elseif ($arrowDirection == "down") {
                $dir = 3;
            } else {
                $dir = 2; // either 'blank' or 'up'
            }
            echo '<br>';
            $this->print_url_arrow('#' . $pid, $pid, $dir);
        }
        echo '</td>';
    }

    /**
     * print the parents table for a family
     *
     * @param Family  $family family gedcom ID
     * @param integer $sosa   child sosa number
     * @param string  $label  indi label (descendancy booklet)
     * @param string  $parid  parent ID (descendancy booklet)
     * @param string  $gparid gd-parent ID (descendancy booklet)
     */
    function print_family_parents(Family $family, $sosa = 0, $label = '', $parid = '', $gparid = '')
    {
        global $pbwidth, $pbheight;

        $husb = $family->getHusband();
        if ($husb) {
            echo '<a name="', $husb->getXref(), '"></a>';
        } else {
            $husb = new Individual('M', "0 @M@ INDI\n1 SEX M", null, WT_GED_ID);
        }
        $wife = $family->getWife();
        if ($wife) {
            echo '<a name="', $wife->getXref(), '"></a>';
        } else {
            $wife = new Individual('F', "0 @F@ INDI\n1 SEX F", null, WT_GED_ID);
        }

        if ($sosa) {
            echo '<p class="name_head">', $family->getFullName(), '</p>';
        }

        /**
         * husband side
         */
        echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr><td rowspan=\"2\">";
        echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\" border=\"0\"><tr>";
        if ($parid) {
            if ($husb->getXref() == $parid) {
                $this->print_sosa_number($label);
            } else {
                $this->print_sosa_number($label, "", "blank");
            }
        } elseif ($sosa) {
            $this->print_sosa_number($sosa * 2);
        }
        if ($husb->isPendingAddtion()) {
            echo '<td valign="top" class="facts_value new">';
        } elseif ($husb->isPendingDeletion()) {
            echo '<td valign="top" class="facts_value old">';
        } else {
            echo '<td valign="top">';
        }
        FunctionsPrint::i()->print_pedigree_person($husb);
        echo "</td></tr></table>";
        echo "</td>";
        // husband’s parents
        $hfam = $husb->getPrimaryChildFamily();
        if ($hfam) {
            // remove the|| test for $sosa
            echo "<td rowspan=\"2\"><img src=\"" . Application::i()->getTheme()
                                                        ->parameter('image-hline') . "\" alt=\"\"></td><td rowspan=\"2\"><img src=\"" . Application::i()->getTheme()
                                                                                                                                             ->parameter('image-vline') . "\" width=\"3\" height=\"" . ($pbheight + 9) . "\" alt=\"\"></td>";
            echo "<td><img class=\"line5\" src=\"" . Application::i()->getTheme()
                                                          ->parameter('image-hline') . "\" alt=\"\"></td><td>";
            // husband’s father
            if ($hfam && $hfam->getHusband()) {
                echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\" border=\"0\"><tr>";
                if ($sosa > 0) {
                    $this->print_sosa_number($sosa * 4, $hfam->getHusband()
                                                      ->getXref(), "down");
                }
                if (!empty($gparid)
                    && $hfam->getHusband()
                            ->getXref() == $gparid
                ) {
                    $this->print_sosa_number(trim(substr($label, 0, -3), ".") . ".");
                }
                echo "<td valign=\"top\">";
                FunctionsPrint::i()->print_pedigree_person(Individual::getInstance($hfam->getHusband()
                                                                   ->getXref()));
                echo "</td></tr></table>";
            } elseif ($hfam && !$hfam->getHusband()) {
                // Empty box for grandfather
                echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\" border=\"0\"><tr>";
                echo '<td valign="top">';
                FunctionsPrint::i()->print_pedigree_person($hfam->getHusband());
                echo '</td></tr></table>';
            }
            echo "</td>";
        }
        if ($hfam && ($sosa != -1)) {
            echo '<td valign="middle" rowspan="2">';
            $this->print_url_arrow(($sosa == 0 ? '?famid=' . $hfam->getXref() . '&amp;ged=' . WT_GEDURL
                : '#' . $hfam->getXref()), $hfam->getXref(), 1);
            echo '</td>';
        }
        if ($hfam) {
            // husband’s mother
            echo "</tr><tr><td><img src=\"" . Application::i()->getTheme()
                                                   ->parameter('image-hline') . "\" alt=\"\"></td><td>";
            if ($hfam && $hfam->getWife()) {
                echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\" border=\"0\"><tr>";
                if ($sosa > 0) {
                    $this->print_sosa_number($sosa * 4 + 1, $hfam->getWife()
                                                          ->getXref(), "down");
                }
                if (!empty($gparid)
                    && $hfam->getWife()
                            ->getXref() == $gparid
                ) {
                    $this->print_sosa_number(trim(substr($label, 0, -3), ".") . ".");
                }
                echo '<td valign="top">';
                FunctionsPrint::i()->print_pedigree_person(Individual::getInstance($hfam->getWife()
                                                                   ->getXref()));
                echo '</td></tr></table>';
            } elseif ($hfam && !$hfam->getWife()) {
                // Empty box for grandmother
                echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\" border=\"0\"><tr>";
                echo '<td valign="top">';
                FunctionsPrint::i()->print_pedigree_person($hfam->getWife());
                echo '</td></tr></table>';
            }
            echo '</td>';
        }
        echo '</tr></table>';
        if ($sosa && $family->canShow()) {
            foreach ($family->getFacts(WT_EVENTS_MARR) as $fact) {
                echo '<a href="', $family->getHtmlUrl(), '" class="details1">';
                echo str_repeat('&nbsp;', 10);
                echo $fact->summary();
                echo '</a>';
            }
        } else {
            echo '<br>';
        }

        /**
         * wife side
         */
        echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr><td rowspan=\"2\">";
        echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\"><tr>";
        if ($parid) {
            if ($wife->getXref() == $parid) {
                $this->print_sosa_number($label);
            } else {
                $this->print_sosa_number($label, "", "blank");
            }
        } elseif ($sosa) {
            $this->print_sosa_number($sosa * 2 + 1);
        }
        if ($wife->isPendingAddtion()) {
            echo '<td valign="top" class="facts_value new">';
        } elseif ($wife->isPendingDeletion()) {
            echo '<td valign="top" class="facts_value old">';
        } else {
            echo '<td valign="top">';
        }
        FunctionsPrint::i()->print_pedigree_person($wife);
        echo "</td></tr></table>";
        echo "</td>";
        // wife’s parents
        $hfam = $wife->getPrimaryChildFamily();

        if ($hfam) {
            echo "<td rowspan=\"2\"><img src=\"" . Application::i()->getTheme()
                                                        ->parameter('image-hline') . "\" alt=\"\"></td><td rowspan=\"2\"><img src=\"" . Application::i()->getTheme()
                                                                                                                                             ->parameter('image-vline') . "\" width=\"3\" height=\"" . ($pbheight + 9) . "\" alt=\"\"></td>";
            echo "<td><img class=\"line5\" src=\"" . Application::i()->getTheme()
                                                          ->parameter('image-hline') . "\" alt=\"\"></td><td>";
            // wife’s father
            if ($hfam && $hfam->getHusband()) {
                echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\"><tr>";
                if ($sosa > 0) {
                    $this->print_sosa_number($sosa * 4 + 2, $hfam->getHusband()
                                                          ->getXref(), "down");
                }
                if (!empty($gparid)
                    && $hfam->getHusband()
                            ->getXref() == $gparid
                ) {
                    $this->print_sosa_number(trim(substr($label, 0, -3), ".") . ".");
                }
                echo "<td valign=\"top\">";
                FunctionsPrint::i()->print_pedigree_person(Individual::getInstance($hfam->getHusband()
                                                                   ->getXref()));
                echo "</td></tr></table>";
            } elseif ($hfam && !$hfam->getHusband()) {
                // Empty box for grandfather
                echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\" border=\"0\"><tr>";
                echo '<td valign="top">';
                FunctionsPrint::i()->print_pedigree_person($hfam->getHusband());
                echo '</td></tr></table>';
            }
            echo "</td>";
        }
        if ($hfam && ($sosa != -1)) {
            echo '<td valign="middle" rowspan="2">';
            $this->print_url_arrow(($sosa == 0 ? '?famid=' . $hfam->getXref() . '&amp;ged=' . WT_GEDURL
                : '#' . $hfam->getXref()), $hfam->getXref(), 1);
            echo '</td>';
        }
        if ($hfam) {
            // wife’s mother
            echo "</tr><tr><td><img src=\"" . Application::i()->getTheme()
                                                   ->parameter('image-hline') . "\" alt=\"\"></td><td>";
            if ($hfam && $hfam->getWife()) {
                echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\"><tr>";
                if ($sosa > 0) {
                    $this->print_sosa_number($sosa * 4 + 3, $hfam->getWife()
                                                          ->getXref(), "down");
                }
                if (!empty($gparid)
                    && $hfam->getWife()
                            ->getXref() == $gparid
                ) {
                    $this->print_sosa_number(trim(substr($label, 0, -3), ".") . ".");
                }
                echo "<td valign=\"top\">";
                FunctionsPrint::i()->print_pedigree_person(Individual::getInstance($hfam->getWife()
                                                                   ->getXref()));
                echo "</td></tr></table>";
            } elseif ($hfam && !$hfam->getWife()) {
                // Empty box for grandmother
                echo "<table style=\"width: " . ($pbwidth) . "px; height: " . $pbheight . "px;\" border=\"0\"><tr>";
                echo '<td valign="top">';
                FunctionsPrint::i()->print_pedigree_person($hfam->getWife());
                echo '</td></tr></table>';
            }
            echo '</td>';
        }
        echo "</tr></table>";
    }

    /**
     * print the children table for a family
     *
     * @param Family  $family  family
     * @param string  $childid child ID
     * @param integer $sosa    child sosa number
     * @param string  $label   indi label (descendancy booklet)
     */
    function print_family_children(Family $family, $childid = '', $sosa = 0, $label = '')
    {
        global $bheight, $pbheight, $show_cousins;

        $children = $family->getChildren();
        $numchil  = count($children);

        echo "<table border=\"0\" cellpadding=\"0\" cellspacing=\"2\"><tr>";
        if ($sosa > 0) {
            echo "<td></td>";
        }
        echo "<td><span class=\"subheaders\">";
        if ($numchil == 0) {
            echo I18N::translate('No children');
        } else {
            echo I18N::plural('%s child', '%s children', $numchil, $numchil);
        }
        echo '</span>';

        if ($sosa == 0 && WT_USER_CAN_EDIT) {
            echo '<br>';
            echo "<a href=\"#\" onclick=\"return add_child_to_family('", $family->getXref(), "', 'U');\">" . I18N::translate('Add a child to this family') . "</a>";
            echo ' <a class="icon-sex_m_15x15" href="#" onclick="return add_child_to_family(\'', $family->getXref(), '\', \'M\');" title="', I18N::translate('son'), '"></a>';
            echo ' <a class="icon-sex_f_15x15" href="#" onclick="return add_child_to_family(\'', $family->getXref(), '\', \'F\');" title="', I18N::translate('daughter'), '"></a>';
            echo '<br><br>';
        }
        echo '</td>';
        if ($sosa > 0) {
            echo '<td></td><td></td>';
        }
        echo '</tr>';

        $nchi = 1;
        if ($children) {
            foreach ($children as $child) {
                echo '<tr>';
                if ($sosa != 0) {
                    if ($child->getXref() == $childid) {
                        $this->print_sosa_number($sosa, $childid);
                    } elseif (empty($label)) {
                        $this->print_sosa_number("");
                    } else {
                        $this->print_sosa_number($label . ($nchi++) . ".");
                    }
                }
                if ($child->isPendingAddtion()) {
                    echo '<td valign="middle" class="new">';
                } elseif ($child->isPendingDeletion()) {
                    echo '<td valign="middle" class="old">';
                } else {
                    echo '<td valign="middle">';
                }
                FunctionsPrint::i()->print_pedigree_person($child);
                echo "</td>";
                if ($sosa != 0) {
                    // loop for all families where current child is a spouse
                    $famids = $child->getSpouseFamilies();


                    $maxfam = count($famids) - 1;
                    for ($f = 0; $f <= $maxfam; $f++) {
                        $famid_child = $famids[$f]->getXref();
                        // multiple marriages
                        if ($f > 0) {
                            echo '</tr><tr><td></td>';
                            echo '<td valign="top"';
                            if (Globals::i()->TEXT_DIRECTION == 'rtl') {
                                echo ' align="left">';
                            } else {
                                echo ' align="right">';
                            }

                            //find out how many cousins there are to establish vertical line on second families
                            $fchildren = $famids[$f]->getChildren();
                            $kids      = count($fchildren);
                            $Pheader   = Application::i()->getTheme()
                                              ->parameter('compact-chart-box-y') * $kids - $bheight;
                            $PBadj     = 6; // default
                            if ($show_cousins > 0) {
                                if (Application::i()->getTheme()
                                         ->parameter('compact-chart-box-y') * $kids > $bheight
                                ) {
                                    $PBadj = $Pheader / 2 + $kids * 4.5;
                                }
                            }

                            if ($PBadj < 0) {
                                $PBadj = 0;
                            }
                            if ($f == $maxfam) {
                                echo "<img height=\"" . ((($bheight / 2)) + $PBadj) . "px\"";
                            } else {
                                echo "<img height=\"" . $pbheight . "px\"";
                            }
                            echo " width=\"3\" src=\"" . Application::i()->getTheme()
                                                              ->parameter('image-vline') . "\" alt=\"\">";
                            echo "</td>";
                        }
                        echo "<td class=\"details1\" valign=\"middle\" align=\"center\">";
                        $spouse = $famids[$f]->getSpouse($child);

                        $marr = $famids[$f]->getFirstFact('MARR');
                        $div  = $famids[$f]->getFirstFact('DIV');
                        if ($marr) {
                            // marriage date
                            echo $marr->getDate()
                                      ->minDate()
                                      ->format('%Y');
                            // divorce date
                            if ($div) {
                                echo '–', $div->getDate()
                                              ->minDate()
                                              ->format('%Y');
                            }
                        }
                        echo "<br><img width=\"100%\" class=\"line5\" height=\"3\" src=\"" . Application::i()->getTheme()
                                                                                                  ->parameter('image-hline') . "\" alt=\"\">";
                        echo "</td>";
                        // spouse information
                        echo "<td style=\"vertical-align: center;";
                        if (!empty($divrec)) {
                            echo " filter:alpha(opacity=40);opacity:0.4;\">";
                        } else {
                            echo "\">";
                        }
                        FunctionsPrint::i()->print_pedigree_person($spouse);
                        echo "</td>";
                        // cousins
                        if ($show_cousins) {
                            $this->print_cousins($famid_child);
                        }
                    }
                }
                echo "</tr>";
            }
        } elseif ($sosa < 1) {
            // message 'no children' except for sosa
            if (preg_match('/\n1 NCHI (\d+)/', $family->getGedcom(), $match) && $match[1] == 0) {
                echo '<tr><td><i class="icon-childless"></i> ' . I18N::translate('This family remained childless') . '</td></tr>';
            }
        }
        echo "</table><br>";
    }

    /**
     * print a family with Sosa-Stradonitz numbering system
     * ($rootid=1, father=2, mother=3 ...)
     *
     * @param string  $famid   family gedcom ID
     * @param string  $childid tree root ID
     * @param integer $sosa    starting sosa number
     * @param string  $label   indi label (descendancy booklet)
     * @param string  $parid   parent ID (descendancy booklet)
     * @param string  $gparid  gd-parent ID (descendancy booklet)
     */
    function print_sosa_family($famid, $childid, $sosa, $label = '', $parid = '', $gparid = '')
    {
        global $pbwidth;

        echo '<hr>';
        echo '<p style="page-break-before: always;">';
        if (!empty($famid)) {
            echo '<a name="', $famid, '"></a>';
        }
        $this->print_family_parents(Family::getInstance($famid), $sosa, $label, $parid, $gparid);
        echo '<br>';
        echo '<table width="95%"><tr><td valign="top" style="width: ', $pbwidth, 'px;">';
        $this->print_family_children(Family::getInstance($famid), $childid, $sosa, $label);
        echo '</td></tr></table>';
        echo '<br>';
    }

    /**
     * print an arrow to a new url
     *
     * @param string  $url   target url
     * @param string  $label arrow label
     * @param integer $dir   arrow direction 0=left 1=right 2=up 3=down (default=2)
     */
    function print_url_arrow($url, $label, $dir = 2)
    {
        if ($url == "") {
            return;
        }

        // arrow direction
        $adir = $dir;
        if (Globals::i()->TEXT_DIRECTION == "rtl" && $dir == 0) {
            $adir = 1;
        }
        if (Globals::i()->TEXT_DIRECTION == "rtl" && $dir == 1) {
            $adir = 0;
        }


        // arrow style     0         1         2         3
        $array_style = array(
            "icon-larrow",
            "icon-rarrow",
            "icon-uarrow",
            "icon-darrow"
        );
        $astyle      = $array_style[$adir];

        // Labels include people’s names, which may contain markup
        echo '<a href="' . $url . '" title="' . strip_tags($label) . '" class="' . $astyle . '"></a>';
    }

    /**
     * builds and returns sosa relationship name in the active language
     *
     * @param string $sosa sosa number
     *
     * @return string
     */
    function get_sosa_name($sosa)
    {
        $path = '';
        while ($sosa > 1) {
            if ($sosa % 2 == 1) {
                $sosa -= 1;
                $path = 'mot' . $path;
            } else {
                $path = 'fat' . $path;
            }
            $sosa /= 2;
        }

        return Functions::i()->get_relationship_name_from_path($path, null, null);
    }

    /**
     * print cousins list
     *
     * @param string $famid family ID
     */
    protected function print_cousins($famid)
    {
        global $show_full, $bheight, $bwidth;

        $family    = Family::getInstance($famid);
        $fchildren = $family->getChildren();

        $kids           = count($fchildren);
        $save_show_full = $show_full;
        $sbheight       = $bheight;
        $sbwidth        = $bwidth;
        if ($save_show_full) {
            $bheight = Application::i()->getTheme()
                            ->parameter('compact-chart-box-y');
            $bwidth  = Application::i()->getTheme()
                            ->parameter('compact-chart-box-x');
        }

        $show_full = false;
        echo '<td valign="middle" height="100%">';
        if ($kids) {
            echo '<table cellspacing="0" cellpadding="0" border="0" ><tr valign="middle">';
            if ($kids > 1) {
                echo '<td rowspan="', $kids, '" valign="middle" align="right"><img width="3px" height="', (($bheight + 9) * ($kids - 1)), 'px" src="', Application::i()->getTheme()
                                                                                                                                                            ->parameter('image-vline'), '" alt=""></td>';
            }
            $ctkids = count($fchildren);
            $i      = 1;
            foreach ($fchildren as $fchil) {
                if ($i == 1) {
                    echo '<td><img width="10px" height="3px" align="top"';
                } else {
                    echo '<td><img width="10px" height="3px"';
                }
                if (Globals::i()->TEXT_DIRECTION == 'ltr') {
                    echo ' style="padding-right: 2px;"';
                } else {
                    echo ' style="padding-left: 2px;"';
                }
                echo ' src="', Application::i()->getTheme()
                                    ->parameter('image-hline'), '" alt=""></td><td>';
                FunctionsPrint::i()->print_pedigree_person($fchil);
                echo '</td></tr>';
                if ($i < $ctkids) {
                    echo '<tr>';
                    $i++;
                }
            }
            echo '</table>';
        } else {
            // If there is known that there are no children (as opposed to no known children)
            if (preg_match('/\n1 NCHI (\d+)/', $family->getGedcom(), $match) && $match[1] == 0) {
                echo ' <i class="icon-childless" title="', I18N::translate('This family remained childless'), '"></i>';
            }
        }
        $show_full = $save_show_full;
        if ($save_show_full) {
            $bheight = $sbheight;
            $bwidth  = $sbwidth;
        }
        echo '</td>';
    }
}