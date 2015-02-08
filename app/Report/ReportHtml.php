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
 * Class ReportHtml
 */
class ReportHtml extends ReportBase
{
    /**
     * Cell padding
     *
     * @var int
     */
    public $cPadding = 2;

    /**
     * Cell height ratio
     *
     * @var float
     */
    public $cellHeightRatio = 1.8;

    /**
     * Current horizontal position
     *
     * @var float
     */
    public $X = 0.0;

    /**
     * Current vertical position
     *
     * @var float
     */
    public $Y = 0.0;

    /**
     * Currently used style name
     *
     * @var string
     */
    public $currentStyle = '';

    /**
     * Page number counter
     *
     * @var int
     */
    public $pageN = 1;

    /**
     * Store the page width without left and right margins
     *
     * In HTML, we don't need this
     *
     * @var float
     */
    public $noMarginWidth = 0.0;

    /**
     * Last cell height
     *
     * @var float
     */
    public $lastCellHeight = 0.0;

    /**
     * LTR or RTL alignement; "left" on LTR, "right" on RTL
     * Used in <div>
     *
     * @var string
     */
    public $alignRTL = 'left';

    /**
     * LTR or RTL entity
     *
     * @var string
     */
    public $entityRTL = '&lrm;';

    /**
     * Largest Font Height is used by TextBox etc.
     *
     * Use this to calculate a the text height.
     * This makes sure that the text fits into the cell/box when different font sizes are used
     *
     * @var int
     */
    public $largestFontHeight = 0;

    /**
     * Keep track of the highest Y position
     *
     * Used with Header div / Body div / Footer div / "addpage" / The bottom of the last image etc.
     *
     * @var float
     */
    public $maxY = 0;

    /** @var ReportBaseElement[] Array of elements in the header */
    public $headerElements = array();

    /** @var ReportBaseElement[] Array of elements in the page header */
    public $pageHeaderElements = array();

    /** @var ReportBaseElement[] Array of elements in the footer */
    public $footerElements = array();

    /** @var ReportBaseElement[] Array of elements in the body */
    public $bodyElements = array();

    /** @var ReportBaseFootnote[] Array of elements in the footer notes */
    public $printedfootnotes = array();

    /**
     * HTML Setup - ReportHtml
     */
    function setup()
    {
        parent::setup();

        // Setting up the correct dimensions if Portrait (default) or Landscape
        if ($this->orientation == "landscape") {
            $tmpw        = $this->pagew;
            $this->pagew = $this->pageh;
            $this->pageh = $tmpw;
        }
        // Store the pagewidth without margins
        $this->noMarginWidth = (int)($this->pagew - $this->leftmargin - $this->rightmargin);
        // If RTL
        if ($this->rtl) {
            $this->alignRTL  = "right";
            $this->entityRTL = "&rlm;";
        }
        // Change the default HTML font name
        $this->defaultFont = "Arial";

        if ($this->showGenText) {
            // The default style name for Generated by.... is 'genby'
            $element = new ReportHtmlCell(0, 10, 0, 'C', '', 'genby', 1, '.', '.', 0, 0, '', '', true);
            $element->addText($this->generatedby);
            $element->setUrl(parent::WT_URL);
            $this->footerElements[] = $element;
        }
    }

    /**
     * @param $element
     *
     * @return mixed
     */
    function addElement($element)
    {
        if ($this->processing == "B") {
            return $this->bodyElements[] = $element;
        } elseif ($this->processing == "H") {
            return $this->headerElements[] = $element;
        } elseif ($this->processing == "F") {
            return $this->footerElements[] = $element;
        }
    }

    /**
     *
     */
    function runPageHeader()
    {
        foreach ($this->pageHeaderElements as $element) {
            if (is_object($element)) {
                $element->render($this);
            } elseif (is_string($element) && $element == "footnotetexts") {
                $this->footnotes();
            } elseif (is_string($element) && $element == "addpage") {
                $this->addPage();
            }
        }
    }

    /**
     *
     */
    function footnotes()
    {
        $this->currentStyle = "";
        if (!empty($this->printedfootnotes)) {
            foreach ($this->printedfootnotes as $element) {
                $element->renderFootnote($this);
            }
        }
    }

    /**
     *
     */
    function run()
    {
        $controller = new SimpleController;
        $controller
            ->setPageTitle($this->title)
            ->pageHeader();

        // Setting up the styles
        echo '<style type="text/css">';
        echo 'body { font: 10px sans-serif;}';
        foreach ($this->Styles as $class => $style) {
            echo '.', $class, ' { ';
            if ($style["font"] == "dejavusans") {
                $style["font"] = $this->defaultFont;
            }
            echo 'font-family: ', $style['font'], '; ';
            echo 'font-size: ', $style['size'], 'pt; ';
            // Case-insensitive
            if (stripos($style['style'], 'B') !== false) {
                echo 'font-weight: bold; ';
            }
            if (stripos($style['style'], 'I') !== false) {
                echo 'font-style: italic; ';
            }
            if (stripos($style['style'], 'U') !== false) {
                echo 'text-decoration: underline; ';
            }
            if (stripos($style['style'], 'D') !== false) {
                echo 'text-decoration: line-through; ';
            }
            echo '}', PHP_EOL;
        }
        unset($class, $style);
        //-- header divider
        echo '</style>', PHP_EOL;
        echo '<div id="headermargin" style="position: relative; top: auto; height: ', $this->headermargin, 'pt; width: ', $this->noMarginWidth, 'pt;"></div>';
        echo '<div id="headerdiv" style="position: relative; top: auto; width: ', $this->noMarginWidth, 'pt;">';
        foreach ($this->headerElements as $element) {
            if (is_object($element)) {
                $element->render($this);
            } elseif (is_string($element) && $element == "footnotetexts") {
                $this->footnotes();
            } elseif (is_string($element) && $element == "addpage") {
                $this->addPage();
            }
        }
        //-- body
        echo '</div>';
        echo '<script>document.getElementById("headerdiv").style.height="', $this->topmargin - $this->headermargin - 6, 'pt";</script>';
        echo '<div id="bodydiv" style="position: relative; top: auto; width: ', $this->noMarginWidth, 'pt; height: 100%;">';
        $this->Y    = 0;
        $this->maxY = 0;
        $this->runPageHeader();
        foreach ($this->bodyElements as $element) {
            if (is_object($element)) {
                $element->render($this);
            } elseif (is_string($element) && $element == "footnotetexts") {
                $this->footnotes();
            } elseif (is_string($element) && $element == "addpage") {
                $this->addPage();
            }
        }
        //-- footer
        echo '</div>';
        echo '<script>document.getElementById("bodydiv").style.height="', $this->maxY, 'pt";</script>';
        echo '<div id="bottommargin" style="position: relative; top: auto; height: ', $this->bottommargin - $this->footermargin, 'pt;width:', $this->noMarginWidth, 'pt;"></div>';
        echo '<div id="footerdiv" style="position: relative; top: auto; width: ', $this->noMarginWidth, 'pt;height:auto;">';
        $this->Y    = 0;
        $this->X    = 0;
        $this->maxY = 0;
        foreach ($this->footerElements as $element) {
            if (is_object($element)) {
                $element->render($this);
            } elseif (is_string($element) && $element == "footnotetexts") {
                $this->footnotes();
            } elseif (is_string($element) && $element == "addpage") {
                $this->addPage();
            }
        }
        echo '</div>';
        echo '<script>document.getElementById("footerdiv").style.height="', $this->maxY, 'pt";</script>';
        echo '<div id="footermargin" style="position: relative; top: auto; height: ', $this->footermargin, 'pt;width:', $this->noMarginWidth, 'pt;"></div>';
    }

    /**
     * Create a new Cell object - ReportHtml
     *
     * @param integer $width   cell width (expressed in points)
     * @param integer $height  cell height (expressed in points)
     * @param mixed   $border  Border style
     * @param string  $align   Text alignement
     * @param string  $bgcolor Background color code
     * @param string  $style   The name of the text style
     * @param integer $ln      Indicates where the current position should go after the call
     * @param mixed   $top     Y-position
     * @param mixed   $left    X-position
     * @param integer $fill    Indicates if the cell background must be painted (1) or transparent (0). Default value:
     *                         0.
     * @param integer $stretch Stretch carachter mode
     * @param string  $bocolor Border color
     * @param string  $tcolor  Text color
     * @param boolean $reseth
     *
     * @return object ReportHtmlCell
     */
    function createCell($width, $height, $border, $align, $bgcolor, $style, $ln, $top, $left, $fill, $stretch, $bocolor, $tcolor, $reseth)
    {
        return new ReportHtmlCell($width, $height, $border, $align, $bgcolor, $style, $ln, $top, $left, $fill, $stretch, $bocolor, $tcolor, $reseth);
    }

    /**
     * @param $width
     * @param $height
     * @param $border
     * @param $bgcolor
     * @param $newline
     * @param $left
     * @param $top
     * @param $pagecheck
     * @param $style
     * @param $fill
     * @param $padding
     * @param $reseth
     *
     * @return ReportHtmlTextbox
     */
    function createTextBox($width, $height, $border, $bgcolor, $newline, $left, $top, $pagecheck, $style, $fill, $padding, $reseth)
    {
        return new ReportHtmlTextbox($width, $height, $border, $bgcolor, $newline, $left, $top, $pagecheck, $style, $fill, $padding, $reseth);
    }

    /**
     * @param $style
     * @param $color
     *
     * @return ReportHtmlText
     */
    function createText($style, $color)
    {
        return new ReportHtmlText($style, $color);
    }

    /**
     * @param string $style
     *
     * @return ReportHtmlFootnote
     */
    function createFootnote($style = "")
    {
        return new ReportHtmlFootnote($style);
    }

    /**
     * @return ReportHtmlPageheader
     */
    function createPageHeader()
    {
        return new ReportHtmlPageheader;
    }

    /**
     * @param $file
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     * @param $align
     * @param $ln
     *
     * @return ReportHtmlImage
     */
    function createImage($file, $x, $y, $w, $h, $align, $ln)
    {
        return new ReportHtmlImage($file, $x, $y, $w, $h, $align, $ln);
    }

    /**
     * @param $mediaobject
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     * @param $align
     * @param $ln
     *
     * @return ReportHtmlImage
     */
    function createImageFromObject($mediaobject, $x, $y, $w, $h, $align, $ln)
    {
        return new ReportHtmlImage($mediaobject->getHtmlUrlDirect('thumb'), $x, $y, $w, $h, $align, $ln);
    }

    /**
     * @param $x1
     * @param $y1
     * @param $x2
     * @param $y2
     *
     * @return ReportHtmlLine
     */
    function createLine($x1, $y1, $x2, $y2)
    {
        return new ReportHtmlLine($x1, $y1, $x2, $y2);
    }

    /**
     * @param $tag
     * @param $attrs
     *
     * @return ReportHtmlHtml
     */
    function createHTML($tag, $attrs)
    {
        return new ReportHtmlHtml($tag, $attrs);
    }

    /**
     * Clear the Header - ReportHtml
     */
    function clearHeader()
    {
        $this->headerElements = array();
    }

    /****************************
     * Local HTML Report functions
     ****************************/

    /**
     * Update the Page Number and set a new Y if max Y is larger - ReportHtml
     */
    function addPage()
    {
        $this->pageN++;
        // Add a little margin to max Y "between pages"
        $this->maxY += 10;
        // If Y is still heigher by any reason...
        if ($this->maxY < $this->Y) {
            // ... update max Y
            $this->maxY = $this->Y;
        } // else update Y so that nothing will be overwritten, like images or cells...
        else {
            $this->Y = $this->maxY;
        }
    }

    /**
     * Uppdate max Y to keep track it incase of a pagebreak - ReportHtml
     *
     * @param float $y
     */
    function addMaxY($y)
    {
        if ($this->maxY < $y) {
            $this->maxY = $y;
        }
    }

    /**
     * @param $element
     *
     * @return integer
     */
    function addPageHeader($element)
    {
        $this->pageHeaderElements[] = $element;

        return count($this->headerElements) - 1;
    }

    /**
     * Checks the Footnote and numbers them - ReportHtml
     *
     * @param object $footnote
     *
     * @return boolean false if not numbered before | object if already numbered
     */
    function checkFootnote($footnote)
    {
        $ct  = count($this->printedfootnotes);
        $i   = 0;
        $val = $footnote->getValue();
        while ($i < $ct) {
            if ($this->printedfootnotes[$i]->getValue() == $val) {
                // If this footnote already exist then set up the numbers for this object
                $footnote->setNum($i + 1);
                $footnote->setAddlink($i + 1);

                return $this->printedfootnotes[$i];
            }
            $i++;
        }
        // If this Footnote has not been set up yet
        $footnote->setNum($ct + 1);
        $footnote->setAddlink($ct + 1);
        $this->printedfootnotes[] = $footnote;

        return false;
    }

    /**
     * Clear the Page Header - ReportHtml
     */
    function clearPageHeader()
    {
        $this->pageHeaderElements = array();
    }

    /**
     * Count the number of lines - ReportHtml
     *
     * @param string $str
     *
     * @return integer Number of lines. 0 if empty line
     */
    function countLines($str)
    {
        if ($str == "") {
            return 0;
        }

        return (substr_count($str, "\n") + 1);
    }

    /**
     * @return string
     */
    function getCurrentStyle()
    {
        return $this->currentStyle;
    }

    /**
     * @return integer
     */
    function getCurrentStyleHeight()
    {
        if (empty($this->currentStyle)) {
            return $this->defaultFontSize;
        }
        $style = $this->getStyle($this->currentStyle);

        return $style["size"];
    }

    /**
     * @param $cellWidth
     *
     * @return integer
     */
    function getFootnotesHeight($cellWidth)
    {
        $h = 0;
        foreach ($this->printedfootnotes as $element) {
            $h += $element->getFootnoteHeight($this, $cellWidth);
        }

        return $h;
    }

    /**
     * Get the maximum width from current position to the margin - ReportHtml
     *
     * @return float
     */
    function getRemainingWidth()
    {
        return $this->noMarginWidth - $this->X;
    }

    /**
     * @return float
     */
    function getPageHeight()
    {
        return $this->pageh - $this->topmargin;
    }

    /**
     * @param $text
     *
     * @return integer
     */
    function getStringWidth($text)
    {
        $style = $this->getStyle($this->currentStyle);

        return mb_strlen($text) * ($style['size'] / 2);
    }

    /**
     * Get a text height in points - ReportHtml
     *
     * @param $str
     *
     * @return integer
     */
    function getTextCellHeight($str)
    {
        // Count the number of lines to calculate the height
        $nl = $this->countLines($str);

        // Calculate the cell height
        return ceil(($this->getCurrentStyleHeight() * $this->cellHeightRatio) * $nl);
    }

    /**
     * Get the current X position - ReportHtml
     *
     * @return float
     */
    function getX()
    {
        return $this->X;
    }

    /**
     * Get the current Y position - ReportHtml
     *
     * @return float
     */
    function getY()
    {
        return $this->Y;
    }

    /**
     * Get the current page number - ReportHtml
     *
     * @return integer
     */
    function pageNo()
    {
        return $this->pageN;
    }

    /**
     * @param $s
     */
    function setCurrentStyle($s)
    {
        $this->currentStyle = $s;
    }

    /**
     * Set the X position - ReportHtml
     *
     * @param float $x
     */
    function setX($x)
    {
        $this->X = $x;
    }

    /**
     * Set the Y position - ReportHtml
     *
     * Also updates Max Y position
     *
     * @param float $y
     */
    function setY($y)
    {
        $this->Y = $y;
        if ($this->maxY < $y) {
            $this->maxY = $y;
        }
    }

    /**
     * Set the X and Y position - ReportHtml
     *
     * Also updates Max Y position
     *
     * @param float $x
     * @param float $y
     */
    function setXy($x, $y)
    {
        $this->setX($x);
        $this->setY($y);
    }

    /**
     * Wrap text - ReportHtml
     *
     * @param string  $str   Text to wrap
     * @param integer $width Width in points the text has to fit into
     *
     * @return string
     */
    function textWrap($str, $width)
    {
        // Calculate the line width
        $lw = (int)($width / ($this->getCurrentStyleHeight() / 2));
        // Wordwrap each line
        $lines = explode("\n", $str);
        // Line Feed counter
        $lfct     = count($lines);
        $wraptext = '';
        foreach ($lines as $line) {
            $wtext = utf8_wordwrap($line, $lw, "\n", true);
            $wraptext .= $wtext;
            // Add a new line as long as it’s not the last line
            if ($lfct > 1) {
                $wraptext .= "\n";
            }
            $lfct--;
        }

        return $wraptext;
    }

    /**
     * Write text - ReportHtml
     *
     * @param string  $text  Text to print
     * @param string  $color HTML RGB color code (Ex: #001122)
     * @param boolean $useclass
     */
    function write($text, $color = "", $useclass = true)
    {
        $style    = $this->getStyle($this->getCurrentStyle());
        $htmlcode = "<span dir=\"Globals::i()->TEXT_DIRECTION\"";
        if ($useclass) {
            $htmlcode .= " class=\"" . $style["name"] . "\"";
        }
        if (!empty($color)) {
            // Check if Text Color is set and if it’s valid HTML color
            if (preg_match("/#?(..)(..)(..)/", $color)) {
                $htmlcode .= " style=\"color:$color;\"";
            }
        }

        $htmlcode .= ">$text</span>";
        $htmlcode = str_replace(array(
                                    "\n",
                                    "> ",
                                    " <"
                                ), array(
                                    "<br>",
                                    ">&nbsp;",
                                    "&nbsp;<"
                                ), $htmlcode);
        echo $htmlcode;
    }

}
