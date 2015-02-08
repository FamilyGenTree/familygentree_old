<?php
namespace Fisharebest\Webtrees;

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
 * Class ReportBaseFootnote
 */
class ReportBaseFootnote extends ReportBaseElement
{
    /**
     * The name of the style for this element
     *
     * @var string
     */
    public $styleName = "";

    /**
     * Numbers for the links
     *
     * @var int
     */
    public $num;

    /**
     * The text that will be printed with the number
     *
     * @var string
     */
    public $numText = "";

    /**
     * Remaining width of a cell
     *
     * @var float User unit (points)
     */
    public $wrapWidthRemaining;

    /**
     * Original width of a cell
     *
     * @var float User unit (points)
     */
    public $wrapWidthCell;

    public $addlink;

    /**
     * @param string $style
     */
    function __construct($style = "")
    {
        $this->text = "";
        if (!empty($style)) {
            $this->styleName = $style;
        } else {
            $this->styleName = "footnote";
        }

        return 0;
    }

    /**
     * @param $t
     *
     * @return integer
     */
    function addText($t)
    {
        $t = trim($t, "\r\n\t");
        $t = str_replace(array(
                             "<br>",
                             "&nbsp;"
                         ), array(
                             "\n",
                             " "
                         ), $t);
        $t = strip_tags($t);
        $t = htmlspecialchars_decode($t);
        $this->text .= $t;

        return 0;
    }

    /**
     * @param $wrapwidth
     * @param $cellwidth
     *
     * @return mixed
     */
    function setWrapWidth($wrapwidth, $cellwidth)
    {
        $this->wrapWidthCell = $cellwidth;
        if (strpos($this->numText, "\n") !== false) {
            $this->wrapWidthRemaining = $cellwidth;
        } else {
            $this->wrapWidthRemaining = $wrapwidth;
        }

        return $this->wrapWidthRemaining;
    }

    /**
     * @param $n
     *
     * @return integer
     */
    function setNum($n)
    {
        $this->num = $n;
        $this->numText = "$n ";

        return 0;
    }

    /**
     * @param $a
     *
     * @return integer
     */
    function setAddlink($a)
    {
        $this->addlink = $a;

        return 0;
    }
}
