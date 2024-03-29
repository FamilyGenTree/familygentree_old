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
 * element handlers array
 *
 * An array of element handler functions
 *
 * @global array $elementHandler
 */
$elementHandler                       = array();
$elementHandler['Report']['start']    = __NAMESPACE__ . '\\reportStartHandler';
$elementHandler['var']['start']       = __NAMESPACE__ . '\\varStartHandler';
$elementHandler['Title']['start']     = __NAMESPACE__ . '\\titleStartHandler';
$elementHandler['Title']['end']       = __NAMESPACE__ . '\\titleEndHandler';
$elementHandler['Description']['end'] = __NAMESPACE__ . '\\descriptionEndHandler';
$elementHandler['Input']['start']     = __NAMESPACE__ . '\\inputStartHandler';
$elementHandler['Input']['end']       = __NAMESPACE__ . '\\inputEndHandler';

$text         = "";
$report_array = array();

/**
 * Class ReportHeader
 *
 * @package Webtrees\LegacyBundle\Legacy
 *
 * @deprecated most likely replaced by Webtrees/LegacyBundle/src/old_app/Report/ReportBase.php
 */
class ReportHeader
{
    /**
     * @var ReportHeader
     */
    protected static $instance;

    /**
     * Singleton protected
     */
    protected function __construct()
    {

    }

    /**
     * @return ReportHeader
     */
    public static function i()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * xml start element handler
     *
     * this function is called whenever a starting element is reached
     *
     * @param resource $parser the resource handler for the xml parser
     * @param string   $name   the name of the xml element parsed
     * @param string[] $attrs  an array of key value pairs for the attributes
     */
    function startElement($parser, $name, $attrs)
    {
        global $elementHandler, $processIfs;

        if (($processIfs == 0) || ($name == "if")) {
            if (isset($elementHandler[$name]["start"])) {
                call_user_func($elementHandler[$name]["start"], $attrs);
            }
        }
    }

    /**
     * xml end element handler
     *
     * this function is called whenever an ending element is reached
     *
     * @param resource $parser the resource handler for the xml parser
     * @param string   $name   the name of the xml element parsed
     */
    function endElement($parser, $name)
    {
        global $elementHandler, $processIfs;

        if (($processIfs == 0) || ($name == "if")) {
            if (isset($elementHandler[$name]["end"])) {
                call_user_func($elementHandler[$name]["end"]);
            }
        }
    }

    /**
     * xml character data handler
     *
     * this function is called whenever raw character data is reached
     * just print it to the screen
     *
     * @param resource $parser the resource handler for the xml parser
     * @param string   $data   the name of the xml element parsed
     */
    function characterData($parser, $data)
    {
        global $text;

        $text .= $data;
    }

    /**
     * @param string[] $attrs
     */
    function reportStartHandler($attrs)
    {
        global $report_array;

        $access = WT_PRIV_PUBLIC;
        if (isset($attrs["access"])) {
            if (isset($$attrs["access"])) {
                $access = $$attrs["access"];
            }
        }
        $report_array["access"] = $access;

        if (isset($attrs["icon"])) {
            $report_array["icon"] = $attrs["icon"];
        } else {
            $report_array["icon"] = "";
        }
    }

    /**
     * @param string[] $attrs
     */
    function varStartHandler($attrs)
    {
        global $text, $fact, $desc, $type;

        $var = $attrs["var"];
        if (!empty($var)) {
            $tfact = $fact;
            if ($fact == "EVEN") {
                $tfact = $type;
            }
            $var = str_replace(array(
                                   "@fact",
                                   "@desc"
                               ), array(
                                   $tfact,
                                   $desc
                               ), $var);
            if (preg_match('/^I18N::number\((.+)\)$/', $var, $match)) {
                $var = I18N::number($match[1]);
            } elseif (preg_match('/^I18N::translate\(\'(.+)\'\)$/', $var, $match)) {
                $var = I18N::translate($match[1]);
            } elseif (preg_match('/^I18N::translate_c\(\'(.+)\', *\'(.+)\'\)$/', $var, $match)) {
                $var = I18N::translate_c($match[1], $match[2]);
            }
            $text .= $var;
        }
    }

    /**
     *
     */
    function titleStartHandler()
    {
        global $text;

        $text = "";
    }

    /**
     *
     */
    function titleEndHandler()
    {
        global $report_array, $text;

        $report_array["title"] = $text;
        $text                  = "";
    }

    /**
     *
     */
    function descriptionEndHandler()
    {
        global $report_array, $text;

        $report_array["description"] = $text;
        $text                        = "";
    }

    /**
     * @param string[] $attrs
     */
    function inputStartHandler($attrs)
    {
        global $input, $text;

        $text             = "";
        $input            = array();
        $input["name"]    = "";
        $input["type"]    = "";
        $input["lookup"]  = "";
        $input["default"] = "";
        $input["value"]   = "";
        $input["options"] = "";
        if (isset($attrs["name"])) {
            $input["name"] = $attrs["name"];
        }
        if (isset($attrs["type"])) {
            $input["type"] = $attrs["type"];
        }
        if (isset($attrs["lookup"])) {
            $input["lookup"] = $attrs["lookup"];
        }
        if (isset($attrs["default"])) {
            if ($attrs["default"] == "NOW") {
                $input["default"] = date("d M Y");
            } else {
                $match = array();
                if (preg_match("/NOW\s*([+\-])\s*(\d+)/", $attrs['default'], $match) > 0) {
                    $plus = 1;
                    if ($match[1] == "-") {
                        $plus = -1;
                    }
                    $input["default"] = date("d M Y", WT_TIMESTAMP + $plus * 60 * 60 * 24 * $match[2]);
                } else {
                    $input["default"] = $attrs["default"];
                }
            }
        }
        if (isset($attrs["options"])) {
            $input["options"] = $attrs["options"];
        }
    }

    /**
     *
     */
    function inputEndHandler()
    {
        global $report_array, $text, $input;

        $input["value"] = $text;
        if (!isset($report_array["inputs"])) {
            $report_array["inputs"] = array();
        }
        $report_array["inputs"][] = $input;
        $text                     = "";
    }
}