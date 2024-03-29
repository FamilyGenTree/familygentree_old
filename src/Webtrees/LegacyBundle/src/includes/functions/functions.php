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
use Fgt\Globals;

class Functions
{
    /**
     * @var Functions
     */
    protected static $instance;

    /**
     * Singleton protected
     */
    protected function __construct()
    {

    }

    /**
     * @return Functions
     */
    public static function i()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Check with the webtrees.net server for the latest version of webtrees.
     * Fetching the remote file can be slow, so check infrequently, and cache the result.
     * Pass the current versions of webtrees, PHP and MySQL, as the response
     * may be different for each.  The server logs are used to generate
     * installation statistics which can be found at http://svn.webtrees.net/statistics.html
     *
     * @return null|string
     */
    function fetch_latest_version()
    {
        $last_update_timestamp = Site::getPreference('LATEST_WT_VERSION_TIMESTAMP');
        if ($last_update_timestamp < WT_TIMESTAMP - 24 * 60 * 60) {
            $row                = Database::i()->prepare("SHOW VARIABLES LIKE 'version'")
                                          ->fetchOneRow();
            $params             = '?w=' . WT_VERSION . '&p=' . PHP_VERSION . '&m=' . $row->value . '&o=' . (DIRECTORY_SEPARATOR === '/'
                    ? 'u' : 'w');
            $latest_version_txt = File::fetchUrl('http://svn.webtrees.net/build/latest-version.txt' . $params);
            if ($latest_version_txt) {
                Site::setPreference('LATEST_WT_VERSION', $latest_version_txt);
                Site::setPreference('LATEST_WT_VERSION_TIMESTAMP', WT_TIMESTAMP);

                return $latest_version_txt;
            } else {
                // Cannot connect to server - use cached version (if we have one)
                return Site::getPreference('LATEST_WT_VERSION');
            }
        } else {
            return Site::getPreference('LATEST_WT_VERSION');
        }
    }

    /**
     * Convert a file upload PHP error code into user-friendly text.
     *
     * @param integer $error_code
     *
     * @return string
     */
    function file_upload_error_text($error_code)
    {
        switch ($error_code) {
            case UPLOAD_ERR_OK:
                return I18N::translate('File successfully uploaded');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                // I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
                return I18N::translate('Uploaded file exceeds the allowed size');
            case UPLOAD_ERR_PARTIAL:
                // I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
                return I18N::translate('File was only partially uploaded, please try again');
            case UPLOAD_ERR_NO_FILE:
                // I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
                return I18N::translate('No file was received.  Please upload again.');
            case UPLOAD_ERR_NO_TMP_DIR:
                // I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
                return I18N::translate('Missing PHP temporary folder');
            case UPLOAD_ERR_CANT_WRITE:
                // I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
                return I18N::translate('PHP failed to write to disk');
            case UPLOAD_ERR_EXTENSION:
                // I18N: PHP internal error message - php.net/manual/en/features.file-upload.errors.php
                return I18N::translate('PHP blocked file by extension');
            default:
                return 'Error: ' . $error_code;
        }
    }

    /**
     * get a gedcom subrecord
     *
     * searches a gedcom record and returns a subrecord of it.  A subrecord is defined starting at a
     * line with level N and all subsequent lines greater than N until the next N level is reached.
     * For example, the following is a BIRT subrecord:
     * <code>1 BIRT
     * 2 DATE 1 JAN 1900
     * 2 PLAC Phoenix, Maricopa, Arizona</code>
     * The following example is the DATE subrecord of the above BIRT subrecord:
     * <code>2 DATE 1 JAN 1900</code>
     *
     * @param integer $level  the N level of the subrecord to get
     * @param string  $tag    a gedcom tag or string to search for in the record (ie 1 BIRT or 2 DATE)
     * @param string  $gedrec the parent gedcom record to search in
     * @param integer $num    this allows you to specify which matching <var>$tag</var> to get.  Oftentimes a
     *                        gedcom record will have more that 1 of the same type of subrecord.  An individual may have
     *                        multiple events for example.  Passing $num=1 would get the first 1.  Passing $num=2 would get
     *                        the second one, etc.
     *
     * @return string the subrecord that was found or an empty string "" if not found.
     */
    function get_sub_record($level, $tag, $gedrec, $num = 1)
    {
        if (empty($gedrec)) {
            return '';
        }
        // -- adding \n before and after gedrec
        $gedrec       = "\n" . $gedrec . "\n";
        $tag          = trim($tag);
        $searchTarget = "~[\n]" . $tag . "[\s]~";
        $ct           = preg_match_all($searchTarget, $gedrec, $match, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        if ($ct == 0) {
            return '';
        }
        if ($ct < $num) {
            return '';
        }
        $pos1 = $match[$num - 1][0][1];
        $pos2 = strpos($gedrec, "\n$level", $pos1 + 1);
        if (!$pos2) {
            $pos2 = strpos($gedrec, "\n1", $pos1 + 1);
        }
        if (!$pos2) {
            $pos2 = strpos($gedrec, "\nWT_", $pos1 + 1); // WT_SPOUSE, WT_FAMILY_ID ...
        }
        if (!$pos2) {
            return ltrim(substr($gedrec, $pos1));
        }
        $subrec = substr($gedrec, $pos1, $pos2 - $pos1);

        return ltrim($subrec);
    }

    /**
     * get CONT lines
     *
     * get the N+1 CONT or CONC lines of a gedcom subrecord
     *
     * @param integer $nlevel the level of the CONT lines to get
     * @param string  $nrec   the gedcom subrecord to search in
     *
     * @return string a string with all CONT lines merged
     */
    function get_cont($nlevel, $nrec)
    {
        $text = '';

        $subrecords = explode("\n", $nrec);
        foreach ($subrecords as $thisSubrecord) {
            if (substr($thisSubrecord, 0, 2) !== $nlevel . ' ') {
                continue;
            }
            $subrecordType = substr($thisSubrecord, 2, 4);
            if ($subrecordType === 'CONT') {
                $text .= "\n" . substr($thisSubrecord, 7);
            }
        }

        return $text;
    }

    /**
     * Sort a list events for the today/upcoming blocks
     *
     * @param array $a
     * @param array $b
     *
     * @return integer
     */
    function event_sort($a, $b)
    {
        if ($a['jd'] == $b['jd']) {
            if ($a['anniv'] == $b['anniv']) {
                return I18N::strcasecmp($a['fact'], $b['fact']);
            } else {
                return $a['anniv'] - $b['anniv'];
            }
        } else {
            return $a['jd'] - $b['jd'];
        }
    }

    /**
     * Sort a list events for the today/upcoming blocks
     *
     * @param array $a
     * @param array $b
     *
     * @return integer
     */
    function event_sort_name($a, $b)
    {
        if ($a['jd'] == $b['jd']) {
            return GedcomRecord::compare($a['record'], $b['record']);
        } else {
            return $a['jd'] - $b['jd'];
        }
    }

    /**
     * A multi-key sort
     * 1. First divide the facts into two arrays one set with dates and one set without dates
     * 2. Sort each of the two new arrays, the date using the compare date function, the non-dated
     * using the compare type function
     * 3. Then merge the arrays back into the original array using the compare type function
     *
     * @param Fact[] $arr
     */
    function sort_facts(&$arr)
    {
        $dated    = array();
        $nondated = array();
        //-- split the array into dated and non-dated arrays
        $order = 0;
        foreach ($arr as $event) {
            $event->sortOrder = $order;
            $order++;
            if ($event->getDate()
                      ->isOk()
            ) {
                $dated[] = $event;
            } else {
                $nondated[] = $event;
            }
        }

        //-- sort each type of array
        usort($dated, __NAMESPACE__ . '\Fact::compareDate');
        usort($nondated, __NAMESPACE__ . '\Fact::compareType');

        //-- merge the arrays back together comparing by Facts
        $dc = count($dated);
        $nc = count($nondated);
        $i  = 0;
        $j  = 0;
        $k  = 0;
        // while there is anything in the dated array continue merging
        while ($i < $dc) {
            // compare each fact by type to merge them in order
            if ($j < $nc && Fact::compareType($dated[$i], $nondated[$j]) > 0) {
                $arr[$k] = $nondated[$j];
                $j++;
            } else {
                $arr[$k] = $dated[$i];
                $i++;
            }
            $k++;
        }

        // get anything that might be left in the nondated array
        while ($j < $nc) {
            $arr[$k] = $nondated[$j];
            $j++;
            $k++;
        }

    }

    /**
     * For close family relationships, such as the families tab and the family navigator
     * Display a tick if both individuals are the same.
     * Stop after 3 steps, because pending edits may mean that there is no longer a
     * relationship to find.
     *
     * @param Individual $person1
     * @param Individual $person2
     *
     * @return string
     */
    function get_close_relationship_name(Individual $person1, Individual $person2)
    {
        if ($person1 === $person2) {
            $label = '<i class="icon-selected" title="' . I18N::translate('self') . '"></i>';
        } else {
            $label = $this->get_relationship_name($this->get_relationship($person1, $person2, true, 3));
        }

        return $label;
    }

    /**
     * For facts on the individual/family pages.
     * Stop after 4 steps, as distant relationships may take a long time to find.
     * Review the limit of 4 if/when the performance of the function is improved.
     *
     * @param Individual $person1
     * @param Individual $person2
     *
     * @return string
     */
    function get_associate_relationship_name(Individual $person1, Individual $person2)
    {
        if ($person1 === $person2) {
            $label = I18N::translate('self');
        } else {
            $label = $this->get_relationship_name($this->get_relationship($person1, $person2, true, 4));
        }

        return $label;
    }

    /**
     * Get relationship between two individuals in the gedcom
     *
     * @param Individual $person1      the person to compute the relationship from
     * @param Individual $person2      the person to compute the relatiohip to
     * @param boolean    $followspouse whether to add spouses to the path
     * @param integer    $maxlength    the maximum length of path
     * @param integer    $path_to_find which path in the relationship to find, 0 is the shortest path, 1 is the next
     *                                 shortest path, etc
     *
     * @return array|bool An array of nodes on the relationship path, or false if no path found
     */
    function get_relationship(Individual $person1, Individual $person2, $followspouse = true, $maxlength = 0, $path_to_find = 0)
    {
        if ($person1 === $person2) {
            return false;
        }

        //-- current path nodes
        $p1nodes = array();
        //-- ids visited
        $visited = array();

        //-- set up first node for person1
        $node1     = array(
            'path'      => array($person1),
            'length'    => 0,
            'indi'      => $person1,
            'relations' => array('self'),
        );
        $p1nodes[] = $node1;

        $visited[$person1->getXref()] = true;

        $found = false;
        while (!$found) {
            //-- search the node list for the shortest path length
            $shortest = -1;
            foreach ($p1nodes as $index => $node) {
                if ($shortest == -1) {
                    $shortest = $index;
                } else {
                    $node1 = $p1nodes[$shortest];
                    if ($node1['length'] > $node['length']) {
                        $shortest = $index;
                    }
                }
            }
            if ($shortest === -1) {
                return false;
            }
            $node = $p1nodes[$shortest];
            if ($maxlength == 0 || count($node['path']) <= $maxlength) {
                $indi = $node['indi'];
                //-- check all parents and siblings of this node
                foreach ($indi->getChildFamilies(WT_PRIV_HIDE) as $family) {
                    $visited[$family->getXref()] = true;
                    foreach ($family->getSpouses(WT_PRIV_HIDE) as $spouse) {
                        if (!isset($visited[$spouse->getXref()])) {
                            $node1 = $node;
                            $node1['length']++;
                            $node1['path'][]      = $spouse;
                            $node1['indi']        = $spouse;
                            $node1['relations'][] = 'parent';
                            $p1nodes[]            = $node1;
                            if ($spouse === $person2) {
                                if ($path_to_find > 0) {
                                    $path_to_find--;
                                } else {
                                    $found   = true;
                                    $resnode = $node1;
                                }
                            } else {
                                $visited[$spouse->getXref()] = true;
                            }
                        }
                    }
                    foreach ($family->getChildren(WT_PRIV_HIDE) as $child) {
                        if (!isset($visited[$child->getXref()])) {
                            $node1 = $node;
                            $node1['length']++;
                            $node1['path'][]      = $child;
                            $node1['indi']        = $child;
                            $node1['relations'][] = 'sibling';
                            $p1nodes[]            = $node1;
                            if ($child === $person2) {
                                if ($path_to_find > 0) {
                                    $path_to_find--;
                                } else {
                                    $found   = true;
                                    $resnode = $node1;
                                }
                            } else {
                                $visited[$child->getXref()] = true;
                            }
                        }
                    }
                }
                //-- check all spouses and children of this node
                foreach ($indi->getSpouseFamilies(WT_PRIV_HIDE) as $family) {
                    $visited[$family->getXref()] = true;
                    if ($followspouse) {
                        foreach ($family->getSpouses(WT_PRIV_HIDE) as $spouse) {
                            if (!in_array($spouse->getXref(), $node1) || !isset($visited[$spouse->getXref()])) {
                                $node1 = $node;
                                $node1['length']++;
                                $node1['path'][]      = $spouse;
                                $node1['indi']        = $spouse;
                                $node1['relations'][] = 'spouse';
                                $p1nodes[]            = $node1;
                                if ($spouse === $person2) {
                                    if ($path_to_find > 0) {
                                        $path_to_find--;
                                    } else {
                                        $found   = true;
                                        $resnode = $node1;
                                    }
                                } else {
                                    $visited[$spouse->getXref()] = true;
                                }
                            }
                        }
                    }
                    foreach ($family->getChildren(WT_PRIV_HIDE) as $child) {
                        if (!isset($visited[$child->getXref()])) {
                            $node1 = $node;
                            $node1['length']++;
                            $node1['path'][]      = $child;
                            $node1['indi']        = $child;
                            $node1['relations'][] = 'child';
                            $p1nodes[]            = $node1;
                            if ($child === $person2) {
                                if ($path_to_find > 0) {
                                    $path_to_find--;
                                } else {
                                    $found   = true;
                                    $resnode = $node1;
                                }
                            } else {
                                $visited[$child->getXref()] = true;
                            }
                        }
                    }
                }
            }
            unset($p1nodes[$shortest]);
        }

        // Convert generic relationships into sex-specific ones.
        foreach ($resnode['path'] as $n => $indi) {
            switch ($resnode['relations'][$n]) {
                case 'parent':
                    switch ($indi->getSex()) {
                        case 'M':
                            $resnode['relations'][$n] = 'father';
                            break;
                        case 'F':
                            $resnode['relations'][$n] = 'mother';
                            break;
                    }
                    break;
                case 'child':
                    switch ($indi->getSex()) {
                        case 'M':
                            $resnode['relations'][$n] = 'son';
                            break;
                        case 'F':
                            $resnode['relations'][$n] = 'daughter';
                            break;
                    }
                    break;
                case 'spouse':
                    switch ($indi->getSex()) {
                        case 'M':
                            $resnode['relations'][$n] = 'husband';
                            break;
                        case 'F':
                            $resnode['relations'][$n] = 'wife';
                            break;
                    }
                    break;
                case 'sibling':
                    switch ($indi->getSex()) {
                        case 'M':
                            $resnode['relations'][$n] = 'brother';
                            break;
                        case 'F':
                            $resnode['relations'][$n] = 'sister';
                            break;
                    }
                    break;
            }
        }

        return $resnode;
    }

    /**
     * Convert the result of Functions::get_relationship() into a relationship name.
     *
     * @param mixed[][] $nodes
     *
     * @return string
     */
    function get_relationship_name($nodes)
    {
        if (!is_array($nodes)) {
            return '';
        }
        $person1 = $nodes['path'][0];
        $person2 = $nodes['path'][count($nodes['path']) - 1];
        $path    = array_slice($nodes['relations'], 1);
        // Look for paths with *specific* names first.
        // Note that every combination must be listed separately, as the same English
        // name can be used for many different relationships.  e.g.
        // brother’s wife & husband’s sister = sister-in-law.
        //
        // $path is an array of the 12 possible gedcom family relationships:
        // mother/father/parent
        // brother/sister/sibling
        // husband/wife/spouse
        // son/daughter/child
        //
        // This is always the shortest path, so “father, daughter” is “half-sister”, not “sister”.
        //
        // This is very repetitive in English, but necessary in order to handle the
        // complexities of other languages.

        // Make each relationship parts the same length, for simpler matching.
        $combined_path = '';
        foreach ($path as $rel) {
            $combined_path .= substr($rel, 0, 3);
        }

        return $this->get_relationship_name_from_path($combined_path, $person1, $person2);
    }

    /**
     * @param integer $n
     * @param string  $sex
     *
     * @return string
     */
    function cousin_name($n, $sex)
    {
        switch ($sex) {
            case 'M':
                switch ($n) {
                    case  1:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'first cousin');
                    case  2:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'second cousin');
                    case  3:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'third cousin');
                    case  4:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'fourth cousin');
                    case  5:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'fifth cousin');
                    case  6:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'sixth cousin');
                    case  7:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'seventh cousin');
                    case  8:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'eighth cousin');
                    case  9:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'ninth cousin');
                    case 10:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'tenth cousin');
                    case 11:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'eleventh cousin');
                    case 12:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'twelfth cousin');
                    case 13:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'thirteenth cousin');
                    case 14:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'fourteenth cousin');
                    case 15:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', 'fifteenth cousin');
                    default:
                        /* I18N: Note that for Italian and Polish, “N’th cousins” are different from English “N’th cousins”, and the software has already generated the correct “N” for your language.  You only need to translate - you do not need to convert.  For other languages, if your cousin rules are different from English, please contact the developers. */
                        return I18N::translate_c('MALE', '%d × cousin', $n);
                }
            case 'F':
                switch ($n) {
                    case  1:
                        return I18N::translate_c('FEMALE', 'first cousin');
                    case  2:
                        return I18N::translate_c('FEMALE', 'second cousin');
                    case  3:
                        return I18N::translate_c('FEMALE', 'third cousin');
                    case  4:
                        return I18N::translate_c('FEMALE', 'fourth cousin');
                    case  5:
                        return I18N::translate_c('FEMALE', 'fifth cousin');
                    case  6:
                        return I18N::translate_c('FEMALE', 'sixth cousin');
                    case  7:
                        return I18N::translate_c('FEMALE', 'seventh cousin');
                    case  8:
                        return I18N::translate_c('FEMALE', 'eighth cousin');
                    case  9:
                        return I18N::translate_c('FEMALE', 'ninth cousin');
                    case 10:
                        return I18N::translate_c('FEMALE', 'tenth cousin');
                    case 11:
                        return I18N::translate_c('FEMALE', 'eleventh cousin');
                    case 12:
                        return I18N::translate_c('FEMALE', 'twelfth cousin');
                    case 13:
                        return I18N::translate_c('FEMALE', 'thirteenth cousin');
                    case 14:
                        return I18N::translate_c('FEMALE', 'fourteenth cousin');
                    case 15:
                        return I18N::translate_c('FEMALE', 'fifteenth cousin');
                    default:
                        return I18N::translate_c('FEMALE', '%d × cousin', $n);
                }
            default:
                switch ($n) {
                    case  1:
                        return I18N::translate_c('MALE/FEMALE', 'first cousin');
                    case  2:
                        return I18N::translate_c('MALE/FEMALE', 'second cousin');
                    case  3:
                        return I18N::translate_c('MALE/FEMALE', 'third cousin');
                    case  4:
                        return I18N::translate_c('MALE/FEMALE', 'fourth cousin');
                    case  5:
                        return I18N::translate_c('MALE/FEMALE', 'fifth cousin');
                    case  6:
                        return I18N::translate_c('MALE/FEMALE', 'sixth cousin');
                    case  7:
                        return I18N::translate_c('MALE/FEMALE', 'seventh cousin');
                    case  8:
                        return I18N::translate_c('MALE/FEMALE', 'eighth cousin');
                    case  9:
                        return I18N::translate_c('MALE/FEMALE', 'ninth cousin');
                    case 10:
                        return I18N::translate_c('MALE/FEMALE', 'tenth cousin');
                    case 11:
                        return I18N::translate_c('MALE/FEMALE', 'eleventh cousin');
                    case 12:
                        return I18N::translate_c('MALE/FEMALE', 'twelfth cousin');
                    case 13:
                        return I18N::translate_c('MALE/FEMALE', 'thirteenth cousin');
                    case 14:
                        return I18N::translate_c('MALE/FEMALE', 'fourteenth cousin');
                    case 15:
                        return I18N::translate_c('MALE/FEMALE', 'fifteenth cousin');
                    default:
                        return I18N::translate_c('MALE/FEMALE', '%d × cousin', $n);
                }
        }
    }

    /**
     * A variation on $this->cousin_name(), for constructs such as “sixth great-nephew”
     * Currently used only by Spanish relationship names.
     *
     * @param integer $n
     * @param string  $sex
     * @param string  $relation
     *
     * @return string
     */
    function cousin_name2($n, $sex, $relation)
    {
        switch ($sex) {
            case 'M':
                switch ($n) {
                    case  1: // I18N: A Spanish relationship name, such as third great-nephew
                        return I18N::translate_c('MALE', 'first %s', $relation);
                    case  2:
                        return I18N::translate_c('MALE', 'second %s', $relation);
                    case  3:
                        return I18N::translate_c('MALE', 'third %s', $relation);
                    case  4:
                        return I18N::translate_c('MALE', 'fourth %s', $relation);
                    case  5:
                        return I18N::translate_c('MALE', 'fifth %s', $relation);
                    default: // I18N: A Spanish relationship name, such as third great-nephew
                        return I18N::translate_c('MALE', '%1$d × %2$s', $n, $relation);
                }
            case 'F':
                switch ($n) {
                    case  1: // I18N: A Spanish relationship name, such as third great-nephew
                        return I18N::translate_c('FEMALE', 'first %s', $relation);
                    case  2:
                        return I18N::translate_c('FEMALE', 'second %s', $relation);
                    case  3:
                        return I18N::translate_c('FEMALE', 'third %s', $relation);
                    case  4:
                        return I18N::translate_c('FEMALE', 'fourth %s', $relation);
                    case  5:
                        return I18N::translate_c('FEMALE', 'fifth %s', $relation);
                    default: // I18N: A Spanish relationship name, such as third great-nephew
                        return I18N::translate_c('FEMALE', '%1$d × %2$s', $n, $relation);
                }
            default:
                switch ($n) {
                    case  1: // I18N: A Spanish relationship name, such as third great-nephew
                        return I18N::translate_c('MALE/FEMALE', 'first %s', $relation);
                    case  2:
                        return I18N::translate_c('MALE/FEMALE', 'second %s', $relation);
                    case  3:
                        return I18N::translate_c('MALE/FEMALE', 'third %s', $relation);
                    case  4:
                        return I18N::translate_c('MALE/FEMALE', 'fourth %s', $relation);
                    case  5:
                        return I18N::translate_c('MALE/FEMALE', 'fifth %s', $relation);
                    default: // I18N: A Spanish relationship name, such as third great-nephew
                        return I18N::translate_c('MALE/FEMALE', '%1$d × %2$s', $n, $relation);
                }
        }
    }

    /**
     * @param string     $path
     * @param Individual $person1
     * @param Individual $person2
     *
     * @return string
     */
    function get_relationship_name_from_path($path, Individual $person1 = null, Individual $person2 = null)
    {
        if (!preg_match('/^(mot|fat|par|hus|wif|spo|son|dau|chi|bro|sis|sib)*$/', $path)) {
            // TODO: Update all the “3 RELA ” values in class_person
            return '<span class="error">' . $path . '</span>';
        }
        // The path does not include the starting person.  In some languages, the
        // translation for a man’s (relative) is different to a woman’s (relative),
        // due to inflection.
        $sex1 = $person1 ? $person1->getSex() : 'U';

        // The sex of the last person in the relationship determines the name in
        // many cases.  e.g. great-aunt / great-uncle
        if (preg_match('/(fat|hus|son|bro)$/', $path)) {
            $sex2 = 'M';
        } elseif (preg_match('/(mot|wif|dau|sis)$/', $path)) {
            $sex2 = 'F';
        } else {
            $sex2 = 'U';
        }

        switch ($path) {
            case '':
                return I18N::translate('self');
            //  Level One relationships
            case 'mot':
                return I18N::translate('mother');
            case 'fat':
                return I18N::translate('father');
            case 'par':
                return I18N::translate('parent');
            case 'hus':
                if ($person1 && $person2) {
                    foreach ($person1->getSpouseFamilies() as $family) {
                        if ($person2 === $family->getSpouse($person1)) {
                            if ($family->getFacts('_NMR')) {
                                if ($family->getFacts(WT_EVENTS_DIV)) {
                                    return I18N::translate_c('MALE', 'ex-partner');
                                } else {
                                    return I18N::translate_c('MALE', 'partner');
                                }
                            } elseif ($family->getFacts(WT_EVENTS_DIV)) {
                                return I18N::translate('ex-husband');
                            }
                        }
                    }
                }

                return I18N::translate('husband');
            case 'wif':
                if ($person1 && $person1) {
                    foreach ($person1->getSpouseFamilies() as $family) {
                        if ($person2 === $family->getSpouse($person1)) {
                            if ($family->getFacts('_NMR')) {
                                if ($family->getFacts(WT_EVENTS_DIV)) {
                                    return I18N::translate_c('FEMALE', 'ex-partner');
                                } else {
                                    return I18N::translate_c('FEMALE', 'partner');
                                }
                            } elseif ($family->getFacts(WT_EVENTS_DIV)) {
                                return I18N::translate('ex-wife');
                            }
                        }
                    }
                }

                return I18N::translate('wife');
            case 'spo':
                if ($person1 && $person2) {
                    foreach ($person1->getSpouseFamilies() as $family) {
                        if ($person2 === $family->getSpouse($person1)) {
                            if ($family->getFacts('_NMR')) {
                                if ($family->getFacts(WT_EVENTS_DIV)) {
                                    return I18N::translate_c('MALE/FEMALE', 'ex-partner');
                                } else {
                                    return I18N::translate_c('MALE/FEMALE', 'partner');
                                }
                            } elseif ($family->getFacts(WT_EVENTS_DIV)) {
                                return I18N::translate('ex-spouse');
                            }
                        }
                    }
                }

                return I18N::translate('spouse');
            case 'son':
                return I18N::translate('son');
            case 'dau':
                return I18N::translate('daughter');
            case 'chi':
                return I18N::translate('child');
            case 'bro':
                if ($person1 && $person2) {
                    $dob1 = $person1->getBirthDate();
                    $dob2 = $person2->getBirthDate();
                    if ($dob1->isOK() && $dob2->isOK()) {
                        if (abs($dob1->JD() - $dob2->JD()) < 2 && !$dob1->qual1 && !$dob2->qual1) {
                            // Exclude BEF, AFT, etc.
                            return I18N::translate('twin brother');
                        } elseif ($dob1->MaxJD() < $dob2->MinJD()) {
                            return I18N::translate('younger brother');
                        } elseif ($dob1->MinJD() > $dob2->MaxJD()) {
                            return I18N::translate('elder brother');
                        }
                    }
                }

                return I18N::translate('brother');
            case 'sis':
                if ($person1 && $person2) {
                    $dob1 = $person1->getBirthDate();
                    $dob2 = $person2->getBirthDate();
                    if ($dob1->isOK() && $dob2->isOK()) {
                        if (abs($dob1->JD() - $dob2->JD()) < 2 && !$dob1->qual1 && !$dob2->qual1) {
                            // Exclude BEF, AFT, etc.
                            return I18N::translate('twin sister');
                        } elseif ($dob1->MaxJD() < $dob2->MinJD()) {
                            return I18N::translate('younger sister');
                        } elseif ($dob1->MinJD() > $dob2->MaxJD()) {
                            return I18N::translate('elder sister');
                        }
                    }
                }

                return I18N::translate('sister');
            case 'sib':
                if ($person1 && $person2) {
                    $dob1 = $person1->getBirthDate();
                    $dob2 = $person2->getBirthDate();
                    if ($dob1->isOK() && $dob2->isOK()) {
                        if (abs($dob1->JD() - $dob2->JD()) < 2 && !$dob1->qual1 && !$dob2->qual1) {
                            // Exclude BEF, AFT, etc.
                            return I18N::translate('twin sibling');
                        } elseif ($dob1->MaxJD() < $dob2->MinJD()) {
                            return I18N::translate('younger sibling');
                        } elseif ($dob1->MinJD() > $dob2->MaxJD()) {
                            return I18N::translate('elder sibling');
                        }
                    }
                }

                return I18N::translate('sibling');

            // Level Two relationships
            case 'brochi':
                return I18N::translate_c('brother’s child', 'nephew/niece');
            case 'brodau':
                return I18N::translate_c('brother’s daughter', 'niece');
            case 'broson':
                return I18N::translate_c('brother’s son', 'nephew');
            case 'browif':
                return I18N::translate_c('brother’s wife', 'sister-in-law');
            case 'chichi':
                return I18N::translate_c('child’s child', 'grandchild');
            case 'chidau':
                return I18N::translate_c('child’s daughter', 'granddaughter');
            case 'chihus':
                return I18N::translate_c('child’s husband', 'son-in-law');
            case 'chison':
                return I18N::translate_c('child’s son', 'grandson');
            case 'chispo':
                return I18N::translate_c('child’s spouse', 'son/daughter-in-law');
            case 'chiwif':
                return I18N::translate_c('child’s wife', 'daughter-in-law');
            case 'dauchi':
                return I18N::translate_c('daughter’s child', 'grandchild');
            case 'daudau':
                return I18N::translate_c('daughter’s daughter', 'granddaughter');
            case 'dauhus':
                return I18N::translate_c('daughter’s husband', 'son-in-law');
            case 'dauson':
                return I18N::translate_c('daughter’s son', 'grandson');
            case 'fatbro':
                return I18N::translate_c('father’s brother', 'uncle');
            case 'fatchi':
                return I18N::translate_c('father’s child', 'half-sibling');
            case 'fatdau':
                return I18N::translate_c('father’s daughter', 'half-sister');
            case 'fatfat':
                return I18N::translate_c('father’s father', 'paternal grandfather');
            case 'fatmot':
                return I18N::translate_c('father’s mother', 'paternal grandmother');
            case 'fatpar':
                return I18N::translate_c('father’s parent', 'paternal grandparent');
            case 'fatsib':
                return I18N::translate_c('father’s sibling', 'aunt/uncle');
            case 'fatsis':
                return I18N::translate_c('father’s sister', 'aunt');
            case 'fatson':
                return I18N::translate_c('father’s son', 'half-brother');
            case 'fatwif':
                return I18N::translate_c('father’s wife', 'step-mother');
            case 'husbro':
                return I18N::translate_c('husband’s brother', 'brother-in-law');
            case 'huschi':
                return I18N::translate_c('husband’s child', 'step-child');
            case 'husdau':
                return I18N::translate_c('husband’s daughter', 'step-daughter');
            case 'husfat':
                return I18N::translate_c('husband’s father', 'father-in-law');
            case 'husmot':
                return I18N::translate_c('husband’s mother', 'mother-in-law');
            case 'hussib':
                return I18N::translate_c('husband’s sibling', 'brother/sister-in-law');
            case 'hussis':
                return I18N::translate_c('husband’s sister', 'sister-in-law');
            case 'husson':
                return I18N::translate_c('husband’s son', 'step-son');
            case 'motbro':
                return I18N::translate_c('mother’s brother', 'uncle');
            case 'motchi':
                return I18N::translate_c('mother’s child', 'half-sibling');
            case 'motdau':
                return I18N::translate_c('mother’s daughter', 'half-sister');
            case 'motfat':
                return I18N::translate_c('mother’s father', 'maternal grandfather');
            case 'mothus':
                return I18N::translate_c('mother’s husband', 'step-father');
            case 'motmot':
                return I18N::translate_c('mother’s mother', 'maternal grandmother');
            case 'motpar':
                return I18N::translate_c('mother’s parent', 'maternal grandparent');
            case 'motsib':
                return I18N::translate_c('mother’s sibling', 'aunt/uncle');
            case 'motsis':
                return I18N::translate_c('mother’s sister', 'aunt');
            case 'motson':
                return I18N::translate_c('mother’s son', 'half-brother');
            case 'parbro':
                return I18N::translate_c('parent’s brother', 'uncle');
            case 'parchi':
                return I18N::translate_c('parent’s child', 'half-sibling');
            case 'pardau':
                return I18N::translate_c('parent’s daughter', 'half-sister');
            case 'parfat':
                return I18N::translate_c('parent’s father', 'grandfather');
            case 'parmot':
                return I18N::translate_c('parent’s mother', 'grandmother');
            case 'parpar':
                return I18N::translate_c('parent’s parent', 'grandparent');
            case 'parsib':
                return I18N::translate_c('parent’s sibling', 'aunt/uncle');
            case 'parsis':
                return I18N::translate_c('parent’s sister', 'aunt');
            case 'parson':
                return I18N::translate_c('parent’s son', 'half-brother');
            case 'parspo':
                return I18N::translate_c('parent’s spouse', 'step-parent');
            case 'sibchi':
                return I18N::translate_c('sibling’s child', 'nephew/niece');
            case 'sibdau':
                return I18N::translate_c('sibling’s daughter', 'niece');
            case 'sibson':
                return I18N::translate_c('sibling’s son', 'nephew');
            case 'sibspo':
                return I18N::translate_c('sibling’s spouse', 'brother/sister-in-law');
            case 'sischi':
                return I18N::translate_c('sister’s child', 'nephew/niece');
            case 'sisdau':
                return I18N::translate_c('sister’s daughter', 'niece');
            case 'sishus':
                return I18N::translate_c('sister’s husband', 'brother-in-law');
            case 'sisson':
                return I18N::translate_c('sister’s son', 'nephew');
            case 'sonchi':
                return I18N::translate_c('son’s child', 'grandchild');
            case 'sondau':
                return I18N::translate_c('son’s daughter', 'granddaughter');
            case 'sonson':
                return I18N::translate_c('son’s son', 'grandson');
            case 'sonwif':
                return I18N::translate_c('son’s wife', 'daughter-in-law');
            case 'spobro':
                return I18N::translate_c('spouse’s brother', 'brother-in-law');
            case 'spochi':
                return I18N::translate_c('spouse’s child', 'step-child');
            case 'spodau':
                return I18N::translate_c('spouse’s daughter', 'step-daughter');
            case 'spofat':
                return I18N::translate_c('spouse’s father', 'father-in-law');
            case 'spomot':
                return I18N::translate_c('spouse’s mother', 'mother-in-law');
            case 'sposis':
                return I18N::translate_c('spouse’s sister', 'sister-in-law');
            case 'sposon':
                return I18N::translate_c('spouse’s son', 'step-son');
            case 'spopar':
                return I18N::translate_c('spouse’s parent', 'mother/father-in-law');
            case 'sposib':
                return I18N::translate_c('spouse’s sibling', 'brother/sister-in-law');
            case 'wifbro':
                return I18N::translate_c('wife’s brother', 'brother-in-law');
            case 'wifchi':
                return I18N::translate_c('wife’s child', 'step-child');
            case 'wifdau':
                return I18N::translate_c('wife’s daughter', 'step-daughter');
            case 'wiffat':
                return I18N::translate_c('wife’s father', 'father-in-law');
            case 'wifmot':
                return I18N::translate_c('wife’s mother', 'mother-in-law');
            case 'wifsib':
                return I18N::translate_c('wife’s sibling', 'brother/sister-in-law');
            case 'wifsis':
                return I18N::translate_c('wife’s sister', 'sister-in-law');
            case 'wifson':
                return I18N::translate_c('wife’s son', 'step-son');

            // Level Three relationships
            // I have commented out some of the unknown-sex relationships that are unlikely to to occur.
            // Feel free to add them in, if you think they might be needed
            case 'brochichi':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) brother’s child’s child', 'great-nephew/niece');
                } else {
                    return I18N::translate_c('(a woman’s) brother’s child’s child', 'great-nephew/niece');
                }
            case 'brochidau':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) brother’s child’s daughter', 'great-niece');
                } else {
                    return I18N::translate_c('(a woman’s) brother’s child’s daughter', 'great-niece');
                }
            case 'brochison':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) brother’s child’s son', 'great-nephew');
                } else {
                    return I18N::translate_c('(a woman’s) brother’s child’s son', 'great-nephew');
                }
            case 'brodauchi':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) brother’s daughter’s child', 'great-nephew/niece');
                } else {
                    return I18N::translate_c('(a woman’s) brother’s daughter’s child', 'great-nephew/niece');
                }
            case 'brodaudau':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) brother’s daughter’s daughter', 'great-niece');
                } else {
                    return I18N::translate_c('(a woman’s) brother’s daughter’s daughter', 'great-niece');
                }
            case 'brodauhus':
                return I18N::translate_c('brother’s daughter’s husband', 'nephew-in-law');
            case 'brodauson':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) brother’s daughter’s son', 'great-nephew');
                } else {
                    return I18N::translate_c('(a woman’s) brother’s daughter’s son', 'great-nephew');
                }
            case 'brosonchi':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) brother’s son’s child', 'great-nephew/niece');
                } else {
                    return I18N::translate_c('(a woman’s) brother’s son’s child', 'great-nephew/niece');
                }
            case 'brosondau':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) brother’s son’s daughter', 'great-niece');
                } else {
                    return I18N::translate_c('(a woman’s) brother’s son’s daughter', 'great-niece');
                }
            case 'brosonson':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) brother’s son’s son', 'great-nephew');
                } else {
                    return I18N::translate_c('(a woman’s) brother’s son’s son', 'great-nephew');
                }
            case 'brosonwif':
                return I18N::translate_c('brother’s son’s wife', 'niece-in-law');
            case 'browifbro':
                return I18N::translate_c('brother’s wife’s brother', 'brother-in-law');
            case 'browifsib':
                return I18N::translate_c('brother’s wife’s sibling', 'brother/sister-in-law');
            case 'browifsis':
                return I18N::translate_c('brother’s wife’s sister', 'sister-in-law');
            case 'chichichi':
                return I18N::translate_c('child’s child’s child', 'great-grandchild');
            case 'chichidau':
                return I18N::translate_c('child’s child’s daughter', 'great-granddaughter');
            case 'chichison':
                return I18N::translate_c('child’s child’s son', 'great-grandson');
            case 'chidauchi':
                return I18N::translate_c('child’s daughter’s child', 'great-grandchild');
            case 'chidaudau':
                return I18N::translate_c('child’s daughter’s daughter', 'great-granddaughter');
            case 'chidauhus':
                return I18N::translate_c('child’s daughter’s husband', 'granddaughter’s husband');
            case 'chidauson':
                return I18N::translate_c('child’s daughter’s son', 'great-grandson');
            case 'chisonchi':
                return I18N::translate_c('child’s son’s child', 'great-grandchild');
            case 'chisondau':
                return I18N::translate_c('child’s son’s daughter', 'great-granddaughter');
            case 'chisonson':
                return I18N::translate_c('child’s son’s son', 'great-grandson');
            case 'chisonwif':
                return I18N::translate_c('child’s son’s wife', 'grandson’s wife');
            case 'dauchichi':
                return I18N::translate_c('daughter’s child’s child', 'great-grandchild');
            case 'dauchidau':
                return I18N::translate_c('daughter’s child’s daughter', 'great-granddaughter');
            case 'dauchison':
                return I18N::translate_c('daughter’s child’s son', 'great-grandson');
            case 'daudauchi':
                return I18N::translate_c('daughter’s daughter’s child', 'great-grandchild');
            case 'daudaudau':
                return I18N::translate_c('daughter’s daughter’s daughter', 'great-granddaughter');
            case 'daudauhus':
                return I18N::translate_c('daughter’s daughter’s husband', 'granddaughter’s husband');
            case 'daudauson':
                return I18N::translate_c('daughter’s daughter’s son', 'great-grandson');
            case 'dauhusfat':
                return I18N::translate_c('daughter’s husband’s father', 'son-in-law’s father');
            case 'dauhusmot':
                return I18N::translate_c('daughter’s husband’s mother', 'son-in-law’s mother');
            case 'dauhuspar':
                return I18N::translate_c('daughter’s husband’s parent', 'son-in-law’s parent');
            case 'dausonchi':
                return I18N::translate_c('daughter’s son’s child', 'great-grandchild');
            case 'dausondau':
                return I18N::translate_c('daughter’s son’s daughter', 'great-granddaughter');
            case 'dausonson':
                return I18N::translate_c('daughter’s son’s son', 'great-grandson');
            case 'dausonwif':
                return I18N::translate_c('daughter’s son’s wife', 'grandson’s wife');
            case 'fatbrochi':
                return I18N::translate_c('father’s brother’s child', 'first cousin');
            case 'fatbrodau':
                return I18N::translate_c('father’s brother’s daughter', 'first cousin');
            case 'fatbroson':
                return I18N::translate_c('father’s brother’s son', 'first cousin');
            case 'fatbrowif':
                return I18N::translate_c('father’s brother’s wife', 'aunt');
            case 'fatfatbro':
                return I18N::translate_c('father’s father’s brother', 'great-uncle');
            case 'fatfatfat':
                return I18N::translate_c('father’s father’s father', 'great-grandfather');
            case 'fatfatmot':
                return I18N::translate_c('father’s father’s mother', 'great-grandmother');
            case 'fatfatpar':
                return I18N::translate_c('father’s father’s parent', 'great-grandparent');
            case 'fatfatsib':
                return I18N::translate_c('father’s father’s sibling', 'great-aunt/uncle');
            case 'fatfatsis':
                return I18N::translate_c('father’s father’s sister', 'great-aunt');
            case 'fatmotbro':
                return I18N::translate_c('father’s mother’s brother', 'great-uncle');
            case 'fatmotfat':
                return I18N::translate_c('father’s mother’s father', 'great-grandfather');
            case 'fatmotmot':
                return I18N::translate_c('father’s mother’s mother', 'great-grandmother');
            case 'fatmotpar':
                return I18N::translate_c('father’s mother’s parent', 'great-grandparent');
            case 'fatmotsib':
                return I18N::translate_c('father’s mother’s sibling', 'great-aunt/uncle');
            case 'fatmotsis':
                return I18N::translate_c('father’s mother’s sister', 'great-aunt');
            case 'fatparbro':
                return I18N::translate_c('father’s parent’s brother', 'great-uncle');
            case 'fatparfat':
                return I18N::translate_c('father’s parent’s father', 'great-grandfather');
            case 'fatparmot':
                return I18N::translate_c('father’s parent’s mother', 'great-grandmother');
            case 'fatparpar':
                return I18N::translate_c('father’s parent’s parent', 'great-grandparent');
            case 'fatparsib':
                return I18N::translate_c('father’s parent’s sibling', 'great-aunt/uncle');
            case 'fatparsis':
                return I18N::translate_c('father’s parent’s sister', 'great-aunt');
            case 'fatsischi':
                return I18N::translate_c('father’s sister’s child', 'first cousin');
            case 'fatsisdau':
                return I18N::translate_c('father’s sister’s daughter', 'first cousin');
            case 'fatsishus':
                return I18N::translate_c('father’s sister’s husband', 'uncle');
            case 'fatsisson':
                return I18N::translate_c('father’s sister’s son', 'first cousin');
            case 'fatwifchi':
                return I18N::translate_c('father’s wife’s child', 'step-sibling');
            case 'fatwifdau':
                return I18N::translate_c('father’s wife’s daughter', 'step-sister');
            case 'fatwifson':
                return I18N::translate_c('father’s wife’s son', 'step-brother');
            case 'husbrowif':
                return I18N::translate_c('husband’s brother’s wife', 'sister-in-law');
            case 'hussishus':
                return I18N::translate_c('husband’s sister’s husband', 'brother-in-law');
            case 'motbrochi':
                return I18N::translate_c('mother’s brother’s child', 'first cousin');
            case 'motbrodau':
                return I18N::translate_c('mother’s brother’s daughter', 'first cousin');
            case 'motbroson':
                return I18N::translate_c('mother’s brother’s son', 'first cousin');
            case 'motbrowif':
                return I18N::translate_c('mother’s brother’s wife', 'aunt');
            case 'motfatbro':
                return I18N::translate_c('mother’s father’s brother', 'great-uncle');
            case 'motfatfat':
                return I18N::translate_c('mother’s father’s father', 'great-grandfather');
            case 'motfatmot':
                return I18N::translate_c('mother’s father’s mother', 'great-grandmother');
            case 'motfatpar':
                return I18N::translate_c('mother’s father’s parent', 'great-grandparent');
            case 'motfatsib':
                return I18N::translate_c('mother’s father’s sibling', 'great-aunt/uncle');
            case 'motfatsis':
                return I18N::translate_c('mother’s father’s sister', 'great-aunt');
            case 'mothuschi':
                return I18N::translate_c('mother’s husband’s child', 'step-sibling');
            case 'mothusdau':
                return I18N::translate_c('mother’s husband’s daughter', 'step-sister');
            case 'mothusson':
                return I18N::translate_c('mother’s husband’s son', 'step-brother');
            case 'motmotbro':
                return I18N::translate_c('mother’s mother’s brother', 'great-uncle');
            case 'motmotfat':
                return I18N::translate_c('mother’s mother’s father', 'great-grandfather');
            case 'motmotmot':
                return I18N::translate_c('mother’s mother’s mother', 'great-grandmother');
            case 'motmotpar':
                return I18N::translate_c('mother’s mother’s parent', 'great-grandparent');
            case 'motmotsib':
                return I18N::translate_c('mother’s mother’s sibling', 'great-aunt/uncle');
            case 'motmotsis':
                return I18N::translate_c('mother’s mother’s sister', 'great-aunt');
            case 'motparbro':
                return I18N::translate_c('mother’s parent’s brother', 'great-uncle');
            case 'motparfat':
                return I18N::translate_c('mother’s parent’s father', 'great-grandfather');
            case 'motparmot':
                return I18N::translate_c('mother’s parent’s mother', 'great-grandmother');
            case 'motparpar':
                return I18N::translate_c('mother’s parent’s parent', 'great-grandparent');
            case 'motparsib':
                return I18N::translate_c('mother’s parent’s sibling', 'great-aunt/uncle');
            case 'motparsis':
                return I18N::translate_c('mother’s parent’s sister', 'great-aunt');
            case 'motsischi':
                return I18N::translate_c('mother’s sister’s child', 'first cousin');
            case 'motsisdau':
                return I18N::translate_c('mother’s sister’s daughter', 'first cousin');
            case 'motsishus':
                return I18N::translate_c('mother’s sister’s husband', 'uncle');
            case 'motsisson':
                return I18N::translate_c('mother’s sister’s son', 'first cousin');
            case 'parbrowif':
                return I18N::translate_c('parent’s brother’s wife', 'aunt');
            case 'parfatbro':
                return I18N::translate_c('parent’s father’s brother', 'great-uncle');
            case 'parfatfat':
                return I18N::translate_c('parent’s father’s father', 'great-grandfather');
            case 'parfatmot':
                return I18N::translate_c('parent’s father’s mother', 'great-grandmother');
            case 'parfatpar':
                return I18N::translate_c('parent’s father’s parent', 'great-grandparent');
            case 'parfatsib':
                return I18N::translate_c('parent’s father’s sibling', 'great-aunt/uncle');
            case 'parfatsis':
                return I18N::translate_c('parent’s father’s sister', 'great-aunt');
            case 'parmotbro':
                return I18N::translate_c('parent’s mother’s brother', 'great-uncle');
            case 'parmotfat':
                return I18N::translate_c('parent’s mother’s father', 'great-grandfather');
            case 'parmotmot':
                return I18N::translate_c('parent’s mother’s mother', 'great-grandmother');
            case 'parmotpar':
                return I18N::translate_c('parent’s mother’s parent', 'great-grandparent');
            case 'parmotsib':
                return I18N::translate_c('parent’s mother’s sibling', 'great-aunt/uncle');
            case 'parmotsis':
                return I18N::translate_c('parent’s mother’s sister', 'great-aunt');
            case 'parparbro':
                return I18N::translate_c('parent’s parent’s brother', 'great-uncle');
            case 'parparfat':
                return I18N::translate_c('parent’s parent’s father', 'great-grandfather');
            case 'parparmot':
                return I18N::translate_c('parent’s parent’s mother', 'great-grandmother');
            case 'parparpar':
                return I18N::translate_c('parent’s parent’s parent', 'great-grandparent');
            case 'parparsib':
                return I18N::translate_c('parent’s parent’s sibling', 'great-aunt/uncle');
            case 'parparsis':
                return I18N::translate_c('parent’s parent’s sister', 'great-aunt');
            case 'parsishus':
                return I18N::translate_c('parent’s sister’s husband', 'uncle');
            case 'parspochi':
                return I18N::translate_c('parent’s spouse’s child', 'step-sibling');
            case 'parspodau':
                return I18N::translate_c('parent’s spouse’s daughter', 'step-sister');
            case 'parsposon':
                return I18N::translate_c('parent’s spouse’s son', 'step-brother');
            case 'sibchichi':
                return I18N::translate_c('sibling’s child’s child', 'great-nephew/niece');
            case 'sibchidau':
                return I18N::translate_c('sibling’s child’s daughter', 'great-niece');
            case 'sibchison':
                return I18N::translate_c('sibling’s child’s son', 'great-nephew');
            case 'sibdauchi':
                return I18N::translate_c('sibling’s daughter’s child', 'great-nephew/niece');
            case 'sibdaudau':
                return I18N::translate_c('sibling’s daughter’s daughter', 'great-niece');
            case 'sibdauhus':
                return I18N::translate_c('sibling’s daughter’s husband', 'nephew-in-law');
            case 'sibdauson':
                return I18N::translate_c('sibling’s daughter’s son', 'great-nephew');
            case 'sibsonchi':
                return I18N::translate_c('sibling’s son’s child', 'great-nephew/niece');
            case 'sibsondau':
                return I18N::translate_c('sibling’s son’s daughter', 'great-niece');
            case 'sibsonson':
                return I18N::translate_c('sibling’s son’s son', 'great-nephew');
            case 'sibsonwif':
                return I18N::translate_c('sibling’s son’s wife', 'niece-in-law');
            case 'sischichi':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) sister’s child’s child', 'great-nephew/niece');
                } else {
                    return I18N::translate_c('(a woman’s) sister’s child’s child', 'great-nephew/niece');
                }
            case 'sischidau':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) sister’s child’s daughter', 'great-niece');
                } else {
                    return I18N::translate_c('(a woman’s) sister’s child’s daughter', 'great-niece');
                }
            case 'sischison':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) sister’s child’s son', 'great-nephew');
                } else {
                    return I18N::translate_c('(a woman’s) sister’s child’s son', 'great-nephew');
                }
            case 'sisdauchi':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) sister’s daughter’s child', 'great-nephew/niece');
                } else {
                    return I18N::translate_c('(a woman’s) sister’s daughter’s child', 'great-nephew/niece');
                }
            case 'sisdaudau':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) sister’s daughter’s daughter', 'great-niece');
                } else {
                    return I18N::translate_c('(a woman’s) sister’s daughter’s daughter', 'great-niece');
                }
            case 'sisdauhus':
                return I18N::translate_c('sisters’s daughter’s husband', 'nephew-in-law');
            case 'sisdauson':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) sister’s daughter’s son', 'great-nephew');
                } else {
                    return I18N::translate_c('(a woman’s) sister’s daughter’s son', 'great-nephew');
                }
            case 'sishusbro':
                return I18N::translate_c('sister’s husband’s brother', 'brother-in-law');
            case 'sishussib':
                return I18N::translate_c('sister’s husband’s sibling', 'brother/sister-in-law');
            case 'sishussis':
                return I18N::translate_c('sister’s husband’s sister', 'sister-in-law');
            case 'sissonchi':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) sister’s son’s child', 'great-nephew/niece');
                } else {
                    return I18N::translate_c('(a woman’s) sister’s son’s child', 'great-nephew/niece');
                }
            case 'sissondau':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) sister’s son’s daughter', 'great-niece');
                } else {
                    return I18N::translate_c('(a woman’s) sister’s son’s daughter', 'great-niece');
                }
            case 'sissonson':
                if ($sex1 === 'M') {
                    return I18N::translate_c('(a man’s) sister’s son’s son', 'great-nephew');
                } else {
                    return I18N::translate_c('(a woman’s) sister’s son’s son', 'great-nephew');
                }
            case 'sissonwif':
                return I18N::translate_c('sisters’s son’s wife', 'niece-in-law');
            case 'sonchichi':
                return I18N::translate_c('son’s child’s child', 'great-grandchild');
            case 'sonchidau':
                return I18N::translate_c('son’s child’s daughter', 'great-granddaughter');
            case 'sonchison':
                return I18N::translate_c('son’s child’s son', 'great-grandson');
            case 'sondauchi':
                return I18N::translate_c('son’s daughter’s child', 'great-grandchild');
            case 'sondaudau':
                return I18N::translate_c('son’s daughter’s daughter', 'great-granddaughter');
            case 'sondauhus':
                return I18N::translate_c('son’s daughter’s husband', 'granddaughter’s husband');
            case 'sondauson':
                return I18N::translate_c('son’s daughter’s son', 'great-grandson');
            case 'sonsonchi':
                return I18N::translate_c('son’s son’s child', 'great-grandchild');
            case 'sonsondau':
                return I18N::translate_c('son’s son’s daughter', 'great-granddaughter');
            case 'sonsonson':
                return I18N::translate_c('son’s son’s son', 'great-grandson');
            case 'sonsonwif':
                return I18N::translate_c('son’s son’s wife', 'grandson’s wife');
            case 'sonwiffat':
                return I18N::translate_c('son’s wife’s father', 'daughter-in-law’s father');
            case 'sonwifmot':
                return I18N::translate_c('son’s wife’s mother', 'daughter-in-law’s mother');
            case 'sonwifpar':
                return I18N::translate_c('son’s wife’s parent', 'daughter-in-law’s parent');
            case 'wifbrowif':
                return I18N::translate_c('wife’s brother’s wife', 'sister-in-law');
            case 'wifsishus':
                return I18N::translate_c('wife’s sister’s husband', 'brother-in-law');

            // Some “special case” level four relationships that have specific names in certain languages
            case 'fatfatbrowif':
                return I18N::translate_c('father’s father’s brother’s wife', 'great-aunt');
            case 'fatfatsibspo':
                return I18N::translate_c('father’s father’s sibling’s spouse', 'great-aunt/uncle');
            case 'fatfatsishus':
                return I18N::translate_c('father’s father’s sister’s husband', 'great-uncle');
            case 'fatmotbrowif':
                return I18N::translate_c('father’s mother’s brother’s wife', 'great-aunt');
            case 'fatmotsibspo':
                return I18N::translate_c('father’s mother’s sibling’s spouse', 'great-aunt/uncle');
            case 'fatmotsishus':
                return I18N::translate_c('father’s mother’s sister’s husband', 'great-uncle');
            case 'fatparbrowif':
                return I18N::translate_c('father’s parent’s brother’s wife', 'great-aunt');
            case 'fatparsibspo':
                return I18N::translate_c('father’s parent’s sibling’s spouse', 'great-aunt/uncle');
            case 'fatparsishus':
                return I18N::translate_c('father’s parent’s sister’s husband', 'great-uncle');
            case 'motfatbrowif':
                return I18N::translate_c('mother’s father’s brother’s wife', 'great-aunt');
            case 'motfatsibspo':
                return I18N::translate_c('mother’s father’s sibling’s spouse', 'great-aunt/uncle');
            case 'motfatsishus':
                return I18N::translate_c('mother’s father’s sister’s husband', 'great-uncle');
            case 'motmotbrowif':
                return I18N::translate_c('mother’s mother’s brother’s wife', 'great-aunt');
            case 'motmotsibspo':
                return I18N::translate_c('mother’s mother’s sibling’s spouse', 'great-aunt/uncle');
            case 'motmotsishus':
                return I18N::translate_c('mother’s mother’s sister’s husband', 'great-uncle');
            case 'motparbrowif':
                return I18N::translate_c('mother’s parent’s brother’s wife', 'great-aunt');
            case 'motparsibspo':
                return I18N::translate_c('mother’s parent’s sibling’s spouse', 'great-aunt/uncle');
            case 'motparsishus':
                return I18N::translate_c('mother’s parent’s sister’s husband', 'great-uncle');
            case 'parfatbrowif':
                return I18N::translate_c('parent’s father’s brother’s wife', 'great-aunt');
            case 'parfatsibspo':
                return I18N::translate_c('parent’s father’s sibling’s spouse', 'great-aunt/uncle');
            case 'parfatsishus':
                return I18N::translate_c('parent’s father’s sister’s husband', 'great-uncle');
            case 'parmotbrowif':
                return I18N::translate_c('parent’s mother’s brother’s wife', 'great-aunt');
            case 'parmotsibspo':
                return I18N::translate_c('parent’s mother’s sibling’s spouse', 'great-aunt/uncle');
            case 'parmotsishus':
                return I18N::translate_c('parent’s mother’s sister’s husband', 'great-uncle');
            case 'parparbrowif':
                return I18N::translate_c('parent’s parent’s brother’s wife', 'great-aunt');
            case 'parparsibspo':
                return I18N::translate_c('parent’s parent’s sibling’s spouse', 'great-aunt/uncle');
            case 'parparsishus':
                return I18N::translate_c('parent’s parent’s sister’s husband', 'great-uncle');
            case 'fatfatbrodau':
                return I18N::translate_c('father’s father’s brother’s daughter', 'first cousin once removed ascending');
            case 'fatfatbroson':
                return I18N::translate_c('father’s father’s brother’s son', 'first cousin once removed ascending');
            case 'fatfatbrochi':
                return I18N::translate_c('father’s father’s brother’s child', 'first cousin once removed ascending');
            case 'fatfatsisdau':
                return I18N::translate_c('father’s father’s sister’s daughter', 'first cousin once removed ascending');
            case 'fatfatsisson':
                return I18N::translate_c('father’s father’s sister’s son', 'first cousin once removed ascending');
            case 'fatfatsischi':
                return I18N::translate_c('father’s father’s sister’s child', 'first cousin once removed ascending');
            case 'fatmotbrodau':
                return I18N::translate_c('father’s mother’s brother’s daughter', 'first cousin once removed ascending');
            case 'fatmotbroson':
                return I18N::translate_c('father’s mother’s brother’s son', 'first cousin once removed ascending');
            case 'fatmotbrochi':
                return I18N::translate_c('father’s mother’s brother’s child', 'first cousin once removed ascending');
            case 'fatmotsisdau':
                return I18N::translate_c('father’s mother’s sister’s daughter', 'first cousin once removed ascending');
            case 'fatmotsisson':
                return I18N::translate_c('father’s mother’s sister’s son', 'first cousin once removed ascending');
            case 'fatmotsischi':
                return I18N::translate_c('father’s mother’s sister’s child', 'first cousin once removed ascending');
            case 'motfatbrodau':
                return I18N::translate_c('mother’s father’s brother’s daughter', 'first cousin once removed ascending');
            case 'motfatbroson':
                return I18N::translate_c('mother’s father’s brother’s son', 'first cousin once removed ascending');
            case 'motfatbrochi':
                return I18N::translate_c('mother’s father’s brother’s child', 'first cousin once removed ascending');
            case 'motfatsisdau':
                return I18N::translate_c('mother’s father’s sister’s daughter', 'first cousin once removed ascending');
            case 'motfatsisson':
                return I18N::translate_c('mother’s father’s sister’s son', 'first cousin once removed ascending');
            case 'motfatsischi':
                return I18N::translate_c('mother’s father’s sister’s child', 'first cousin once removed ascending');
            case 'motmotbrodau':
                return I18N::translate_c('mother’s mother’s brother’s daughter', 'first cousin once removed ascending');
            case 'motmotbroson':
                return I18N::translate_c('mother’s mother’s brother’s son', 'first cousin once removed ascending');
            case 'motmotbrochi':
                return I18N::translate_c('mother’s mother’s brother’s child', 'first cousin once removed ascending');
            case 'motmotsisdau':
                return I18N::translate_c('mother’s mother’s sister’s daughter', 'first cousin once removed ascending');
            case 'motmotsisson':
                return I18N::translate_c('mother’s mother’s sister’s son', 'first cousin once removed ascending');
            case 'motmotsischi':
                return I18N::translate_c('mother’s mother’s sister’s child', 'first cousin once removed ascending');
        }

        // Some “special case” level five relationships that have specific names in certain languages
        if (preg_match('/^(mot|fat|par)fatbro(son|dau|chi)dau$/', $path)) {
            return I18N::translate_c('grandfather’s brother’s granddaughter', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)fatbro(son|dau|chi)son$/', $path)) {
            return I18N::translate_c('grandfather’s brother’s grandson', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)fatbro(son|dau|chi)chi$/', $path)) {
            return I18N::translate_c('grandfather’s brother’s grandchild', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)fatsis(son|dau|chi)dau$/', $path)) {
            return I18N::translate_c('grandfather’s sister’s granddaughter', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)fatsis(son|dau|chi)son$/', $path)) {
            return I18N::translate_c('grandfather’s sister’s grandson', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)fatsis(son|dau|chi)chi$/', $path)) {
            return I18N::translate_c('grandfather’s sister’s grandchild', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)fatsib(son|dau|chi)dau$/', $path)) {
            return I18N::translate_c('grandfather’s sibling’s granddaughter', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)fatsib(son|dau|chi)son$/', $path)) {
            return I18N::translate_c('grandfather’s sibling’s grandson', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)fatsib(son|dau|chi)chi$/', $path)) {
            return I18N::translate_c('grandfather’s sibling’s grandchild', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)motbro(son|dau|chi)dau$/', $path)) {
            return I18N::translate_c('grandmother’s brother’s granddaughter', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)motbro(son|dau|chi)son$/', $path)) {
            return I18N::translate_c('grandmother’s brother’s grandson', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)motbro(son|dau|chi)chi$/', $path)) {
            return I18N::translate_c('grandmother’s brother’s grandchild', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)motsis(son|dau|chi)dau$/', $path)) {
            return I18N::translate_c('grandmother’s sister’s granddaughter', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)motsis(son|dau|chi)son$/', $path)) {
            return I18N::translate_c('grandmother’s sister’s grandson', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)motsis(son|dau|chi)chi$/', $path)) {
            return I18N::translate_c('grandmother’s sister’s grandchild', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)motsib(son|dau|chi)dau$/', $path)) {
            return I18N::translate_c('grandmother’s sibling’s granddaughter', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)motsib(son|dau|chi)son$/', $path)) {
            return I18N::translate_c('grandmother’s sibling’s grandson', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)motsib(son|dau|chi)chi$/', $path)) {
            return I18N::translate_c('grandmother’s sibling’s grandchild', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)parbro(son|dau|chi)dau$/', $path)) {
            return I18N::translate_c('grandparent’s brother’s granddaughter', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)parbro(son|dau|chi)son$/', $path)) {
            return I18N::translate_c('grandparent’s brother’s grandson', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)parbro(son|dau|chi)chi$/', $path)) {
            return I18N::translate_c('grandparent’s brother’s grandchild', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)parsis(son|dau|chi)dau$/', $path)) {
            return I18N::translate_c('grandparent’s sister’s granddaughter', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)parsis(son|dau|chi)son$/', $path)) {
            return I18N::translate_c('grandparent’s sister’s grandson', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)parsis(son|dau|chi)chi$/', $path)) {
            return I18N::translate_c('grandparent’s sister’s grandchild', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)parsib(son|dau|chi)dau$/', $path)) {
            return I18N::translate_c('grandparent’s sibling’s granddaughter', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)parsib(son|dau|chi)son$/', $path)) {
            return I18N::translate_c('grandparent’s sibling’s grandson', 'second cousin');
        } elseif (preg_match('/^(mot|fat|par)parsib(son|dau|chi)chi$/', $path)) {
            return I18N::translate_c('grandparent’s sibling’s grandchild', 'second cousin');
        }

        // Look for generic/pattern relationships.
        // TODO: these are heavily based on English relationship names.
        // We need feedback from other languages to improve this.
        // Dutch has special names for 8 generations of great-great-..., so these need explicit naming
        // Spanish has special names for four but also has two different numbering patterns

        if (preg_match('/^((?:mot|fat|par)+)(bro|sis|sib)$/', $path, $match)) {
            // siblings of direct ancestors
            $up       = strlen($match[1]) / 3;
            $bef_last = substr($path, -6, 3);
            switch ($up) {
                case 3:
                    switch ($sex2) {
                        case 'M':
                            if ($bef_last === 'fat') {
                                return I18N::translate_c('great-grandfather’s brother', 'great-great-uncle');
                            } elseif ($bef_last === 'mot') {
                                return I18N::translate_c('great-grandmother’s brother', 'great-great-uncle');
                            } else {
                                return I18N::translate_c('great-grandparent’s brother', 'great-great-uncle');
                            }
                        case 'F':
                            return I18N::translate('great-great-aunt');
                        default:
                            return I18N::translate('great-great-aunt/uncle');
                    }
                case 4:
                    switch ($sex2) {
                        case 'M':
                            if ($bef_last === 'fat') {
                                return I18N::translate_c('great-great-grandfather’s brother', 'great-great-great-uncle');
                            } elseif ($bef_last === 'mot') {
                                return I18N::translate_c('great-great-grandmother’s brother', 'great-great-great-uncle');
                            } else {
                                return I18N::translate_c('great-great-grandparent’s brother', 'great-great-great-uncle');
                            }
                        case 'F':
                            return I18N::translate('great-great-great-aunt');
                        default:
                            return I18N::translate('great-great-great-aunt/uncle');
                    }
                case 5:
                    switch ($sex2) {
                        case 'M':
                            if ($bef_last === 'fat') {
                                return I18N::translate_c('great-great-great-grandfather’s brother', 'great ×4 uncle');
                            } elseif ($bef_last === 'mot') {
                                return I18N::translate_c('great-great-great-grandmother’s brother', 'great ×4 uncle');
                            } else {
                                return I18N::translate_c('great-great-great-grandparent’s brother', 'great ×4 uncle');
                            }
                        case 'F':
                            return I18N::translate('great ×4 aunt');
                        default:
                            return I18N::translate('great ×4 aunt/uncle');
                    }
                case 6:
                    switch ($sex2) {
                        case 'M':
                            if ($bef_last === 'fat') {
                                return I18N::translate_c('great ×4 grandfather’s brother', 'great ×5 uncle');
                            } elseif ($bef_last === 'mot') {
                                return I18N::translate_c('great ×4 grandmother’s brother', 'great ×5 uncle');
                            } else {
                                return I18N::translate_c('great ×4 grandparent’s brother', 'great ×5 uncle');
                            }
                        case 'F':
                            return I18N::translate('great ×5 aunt');
                        default:
                            return I18N::translate('great ×5 aunt/uncle');
                    }
                case 7:
                    switch ($sex2) {
                        case 'M':
                            if ($bef_last === 'fat') {
                                return I18N::translate_c('great ×5 grandfather’s brother', 'great ×6 uncle');
                            } elseif ($bef_last === 'mot') {
                                return I18N::translate_c('great ×5 grandmother’s brother', 'great ×6 uncle');
                            } else {
                                return I18N::translate_c('great ×5 grandparent’s brother', 'great ×6 uncle');
                            }
                        case 'F':
                            return I18N::translate('great ×6 aunt');
                        default:
                            return I18N::translate('great ×6 aunt/uncle');
                    }
                case 8:
                    switch ($sex2) {
                        case 'M':
                            if ($bef_last === 'fat') {
                                return I18N::translate_c('great ×6 grandfather’s brother', 'great ×7 uncle');
                            } elseif ($bef_last === 'mot') {
                                return I18N::translate_c('great ×6 grandmother’s brother', 'great ×7 uncle');
                            } else {
                                return I18N::translate_c('great ×6 grandparent’s brother', 'great ×7 uncle');
                            }
                        case 'F':
                            return I18N::translate('great ×7 aunt');
                        default:
                            return I18N::translate('great ×7 aunt/uncle');
                    }
                default:
                    // Different languages have different rules for naming generations.
                    // An English great ×12 uncle is a Danish great ×10 uncle.
                    //
                    // Need to find out which languages use which rules.
                    switch (WT_LOCALE) {
                        case 'da':
                            switch ($sex2) {
                                case 'M':
                                    return I18N::translate('great ×%d uncle', $up - 4);
                                case 'F':
                                    return I18N::translate('great ×%d aunt', $up - 4);
                                default:
                                    return I18N::translate('great ×%d aunt/uncle', $up - 4);
                            }
                        case 'pl':
                            switch ($sex2) {
                                case 'M':
                                    if ($bef_last === 'fat') {
                                        return I18N::translate_c('great ×(%d-1) grandfather’s brother', 'great ×%d uncle', $up - 2);
                                    } elseif ($bef_last === 'mot') {
                                        return I18N::translate_c('great ×(%d-1) grandmother’s brother', 'great ×%d uncle', $up - 2);
                                    } else {
                                        return I18N::translate_c('great ×(%d-1) grandparent’s brother', 'great ×%d uncle', $up - 2);
                                    }
                                case 'F':
                                    return I18N::translate('great ×%d aunt', $up - 2);
                                default:
                                    return I18N::translate('great ×%d aunt/uncle', $up - 2);
                            }
                        case 'it': // Source: Michele Locati
                        case 'en_AU':
                        case 'en_GB':
                        case 'en_US':
                        default:
                            switch ($sex2) {
                                case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
                                    return I18N::translate('great ×%d uncle', $up - 1);
                                case 'F':
                                    return I18N::translate('great ×%d aunt', $up - 1);
                                default:
                                    return I18N::translate('great ×%d aunt/uncle', $up - 1);
                            }
                    }
            }
        }
        if (preg_match('/^(?:bro|sis|sib)((?:son|dau|chi)+)$/', $path, $match)) {
            // direct descendants of siblings
            $down  = strlen($match[1]) / 3 + 1; // Add one, as we count generations from the common ancestor
            $first = substr($path, 0, 3);
            switch ($down) {
                case 4:
                    switch ($sex2) {
                        case 'M':
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great-grandson', 'great-great-nephew');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great-grandson', 'great-great-nephew');
                            } else {
                                return I18N::translate_c('(a woman’s) great-great-nephew', 'great-great-nephew');
                            }
                        case 'F':
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great-granddaughter', 'great-great-niece');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great-granddaughter', 'great-great-niece');
                            } else {
                                return I18N::translate_c('(a woman’s) great-great-niece', 'great-great-niece');
                            }
                        default:
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great-grandchild', 'great-great-nephew/niece');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great-grandchild', 'great-great-nephew/niece');
                            } else {
                                return I18N::translate_c('(a woman’s) great-great-nephew/niece', 'great-great-nephew/niece');
                            }
                    }
                case 5:
                    switch ($sex2) {
                        case 'M':
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great-great-grandson', 'great-great-great-nephew');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great-great-grandson', 'great-great-great-nephew');
                            } else {
                                return I18N::translate_c('(a woman’s) great-great-great-nephew', 'great-great-great-nephew');
                            }
                        case 'F':
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great-great-granddaughter', 'great-great-great-niece');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great-great-granddaughter', 'great-great-great-niece');
                            } else {
                                return I18N::translate_c('(a woman’s) great-great-great-niece', 'great-great-great-niece');
                            }
                        default:
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great-great-grandchild', 'great-great-great-nephew/niece');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great-great-grandchild', 'great-great-great-nephew/niece');
                            } else {
                                return I18N::translate_c('(a woman’s) great-great-great-nephew/niece', 'great-great-great-nephew/niece');
                            }
                    }
                case 6:
                    switch ($sex2) {
                        case 'M':
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great-great-great-grandson', 'great ×4 nephew');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great-great-great-grandson', 'great ×4 nephew');
                            } else {
                                return I18N::translate_c('(a woman’s) great ×4 nephew', 'great ×4 nephew');
                            }
                        case 'F':
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great-great-great-granddaughter', 'great ×4 niece');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great-great-great-granddaughter', 'great ×4 niece');
                            } else {
                                return I18N::translate_c('(a woman’s) great ×4 niece', 'great ×4 niece');
                            }
                        default:
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great-great-great-grandchild', 'great ×4 nephew/niece');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great-great-great-grandchild', 'great ×4 nephew/niece');
                            } else {
                                return I18N::translate_c('(a woman’s) great ×4 nephew/niece', 'great ×4 nephew/niece');
                            }
                    }
                case 7:
                    switch ($sex2) {
                        case 'M':
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great ×4 grandson', 'great ×5 nephew');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great ×4 grandson', 'great ×5 nephew');
                            } else {
                                return I18N::translate_c('(a woman’s) great ×5 nephew', 'great ×5 nephew');
                            }
                        case 'F':
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great ×4 granddaughter', 'great ×5 niece');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great ×4 granddaughter', 'great ×5 niece');
                            } else {
                                return I18N::translate_c('(a woman’s) great ×5 niece', 'great ×5 niece');
                            }
                        default:
                            if ($first === 'bro' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) brother’s great ×4 grandchild', 'great ×5 nephew/niece');
                            } elseif ($first === 'sis' && $sex1 === 'M') {
                                return I18N::translate_c('(a man’s) sister’s great ×4 grandchild', 'great ×5 nephew/niece');
                            } else {
                                return I18N::translate_c('(a woman’s) great ×5 nephew/niece', 'great ×5 nephew/niece');
                            }
                    }
                default:
                    // Different languages have different rules for naming generations.
                    // An English great ×12 nephew is a Polish great ×11 nephew.
                    //
                    // Need to find out which languages use which rules.
                    switch (WT_LOCALE) {
                        case 'pl': // Source: Lukasz Wilenski
                            switch ($sex2) {
                                case 'M':
                                    if ($first === 'bro' && $sex1 === 'M') {
                                        return I18N::translate_c('(a man’s) brother’s great ×(%d-1) grandson', 'great ×%d nephew', $down - 3);
                                    } elseif ($first === 'sis' && $sex1 === 'M') {
                                        return I18N::translate_c('(a man’s) sister’s great ×(%d-1) grandson', 'great ×%d nephew', $down - 3);
                                    } else {
                                        return I18N::translate_c('(a woman’s) great ×%d nephew', 'great ×%d nephew', $down - 3);
                                    }
                                case 'F':
                                    if ($first === 'bro' && $sex1 === 'M') {
                                        return I18N::translate_c('(a man’s) brother’s great ×(%d-1) granddaughter', 'great ×%d niece', $down - 3);
                                    } elseif ($first === 'sis' && $sex1 === 'M') {
                                        return I18N::translate_c('(a man’s) sister’s great ×(%d-1) granddaughter', 'great ×%d niece', $down - 3);
                                    } else {
                                        return I18N::translate_c('(a woman’s) great ×%d niece', 'great ×%d niece', $down - 3);
                                    }
                                default:
                                    if ($first === 'bro' && $sex1 === 'M') {
                                        return I18N::translate_c('(a man’s) brother’s great ×(%d-1) grandchild', 'great ×%d nephew/niece', $down - 3);
                                    } elseif ($first === 'sis' && $sex1 === 'M') {
                                        return I18N::translate_c('(a man’s) sister’s great ×(%d-1) grandchild', 'great ×%d nephew/niece', $down - 3);
                                    } else {
                                        return I18N::translate_c('(a woman’s) great ×%d nephew/niece', 'great ×%d nephew/niece', $down - 3);
                                    }
                            }
                        case 'he': // Source: Meliza Amity
                            switch ($sex2) {
                                case 'M':
                                    return I18N::translate('great ×%d nephew', $down - 1);
                                case 'F':
                                    return I18N::translate('great ×%d niece', $down - 1);
                                default:
                                    return I18N::translate('great ×%d nephew/niece', $down - 1);
                            }
                        case 'it': // Source: Michele Locati.
                        case 'en_AU':
                        case 'en_GB':
                        case 'en_US':
                        default:
                            switch ($sex2) {
                                case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
                                    return I18N::translate('great ×%d nephew', $down - 2);
                                case 'F':
                                    return I18N::translate('great ×%d niece', $down - 2);
                                default:
                                    return I18N::translate('great ×%d nephew/niece', $down - 2);
                            }
                    }
            }
        }
        if (preg_match('/^((?:mot|fat|par)*)$/', $path, $match)) {
            // direct ancestors
            $up = strlen($match[1]) / 3;
            switch ($up) {
                case 4:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great-great-grandfather');
                        case 'F':
                            return I18N::translate('great-great-grandmother');
                        default:
                            return I18N::translate('great-great-grandparent');
                    }
                case 5:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great-great-great-grandfather');
                        case 'F':
                            return I18N::translate('great-great-great-grandmother');
                        default:
                            return I18N::translate('great-great-great-grandparent');
                    }
                case 6:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great ×4 grandfather');
                        case 'F':
                            return I18N::translate('great ×4 grandmother');
                        default:
                            return I18N::translate('great ×4 grandparent');
                    }
                case 7:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great ×5 grandfather');
                        case 'F':
                            return I18N::translate('great ×5 grandmother');
                        default:
                            return I18N::translate('great ×5 grandparent');
                    }
                case 8:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great ×6 grandfather');
                        case 'F':
                            return I18N::translate('great ×6 grandmother');
                        default:
                            return I18N::translate('great ×6 grandparent');
                    }
                case 9:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great ×7 grandfather');
                        case 'F':
                            return I18N::translate('great ×7 grandmother');
                        default:
                            return I18N::translate('great ×7 grandparent');
                    }
                default:
                    // Different languages have different rules for naming generations.
                    // An English great ×12 grandfather is a Danish great ×11 grandfather.
                    //
                    // Need to find out which languages use which rules.
                    switch (WT_LOCALE) {
                        case 'da': // Source: Patrick Sorensen
                            switch ($sex2) {
                                case 'M':
                                    return I18N::translate('great ×%d grandfather', $up - 3);
                                case 'F':
                                    return I18N::translate('great ×%d grandmother', $up - 3);
                                default:
                                    return I18N::translate('great ×%d grandparent', $up - 3);
                            }
                        case 'it': // Source: Michele Locati
                        case 'es': // Source: Wes Groleau
                            switch ($sex2) {
                                case 'M':
                                    return I18N::translate('great ×%d grandfather', $up);
                                case 'F':
                                    return I18N::translate('great ×%d grandmother', $up);
                                default:
                                    return I18N::translate('great ×%d grandparent', $up);
                            }
                        case 'fr': // Source: Jacqueline Tetreault
                        case 'fr_CA':
                            switch ($sex2) {
                                case 'M':
                                    return I18N::translate('great ×%d grandfather', $up - 1);
                                case 'F':
                                    return I18N::translate('great ×%d grandmother', $up - 1);
                                default:
                                    return I18N::translate('great ×%d grandparent', $up - 1);
                            }
                        case 'nn': // Source: Hogne Røed Nilsen (https://bugs.launchpad.net/webtrees/+bug/1168553)
                        case 'nb':
                            switch ($sex2) {
                                case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
                                    return I18N::translate('great ×%d grandfather', $up - 3);
                                case 'F':
                                    return I18N::translate('great ×%d grandmother', $up - 3);
                                default:
                                    return I18N::translate('great ×%d grandparent', $up - 3);
                            }
                        case 'en_AU':
                        case 'en_GB':
                        case 'en_US':
                        default:
                            switch ($sex2) {
                                case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
                                    return I18N::translate('great ×%d grandfather', $up - 2);
                                case 'F':
                                    return I18N::translate('great ×%d grandmother', $up - 2);
                                default:
                                    return I18N::translate('great ×%d grandparent', $up - 2);
                            }
                    }
            }
        }
        if (preg_match('/^((?:son|dau|chi)*)$/', $path, $match)) {
            // direct descendants
            $up = strlen($match[1]) / 3;
            switch ($up) {
                case 4:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great-great-grandson');
                        case 'F':
                            return I18N::translate('great-great-granddaughter');
                        default:
                            return I18N::translate('great-great-grandchild');
                    }
                    break;
                case 5:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great-great-great-grandson');
                        case 'F':
                            return I18N::translate('great-great-great-granddaughter');
                        default:
                            return I18N::translate('great-great-great-grandchild');
                    }
                    break;
                case 6:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great ×4 grandson');
                        case 'F':
                            return I18N::translate('great ×4 granddaughter');
                        default:
                            return I18N::translate('great ×4 grandchild');
                    }
                    break;
                case 7:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great ×5 grandson');
                        case 'F':
                            return I18N::translate('great ×5 granddaughter');
                        default:
                            return I18N::translate('great ×5 grandchild');
                    }
                    break;
                case 8:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great ×6 grandson');
                        case 'F':
                            return I18N::translate('great ×6 granddaughter');
                        default:
                            return I18N::translate('great ×6 grandchild');
                    }
                    break;
                case 9:
                    switch ($sex2) {
                        case 'M':
                            return I18N::translate('great ×7 grandson');
                        case 'F':
                            return I18N::translate('great ×7 granddaughter');
                        default:
                            return I18N::translate('great ×7 grandchild');
                    }
                    break;
                default:
                    // Different languages have different rules for naming generations.
                    // An English great ×12 grandson is a Danish great ×11 grandson.
                    //
                    // Need to find out which languages use which rules.
                    switch (WT_LOCALE) {
                        case 'nn': // Source: Hogne Røed Nilsen
                        case 'nb':
                        case 'da': // Source: Patrick Sorensen
                            switch ($sex2) {
                                case 'M':
                                    return I18N::translate('great ×%d grandson', $up - 3);
                                case 'F':
                                    return I18N::translate('great ×%d granddaughter', $up - 3);
                                default:
                                    return I18N::translate('great ×%d grandchild', $up - 3);
                            }
                        case 'it': // Source: Michele Locati
                        case 'es': // Source: Wes Groleau (adding doesn’t change behavior, but needs to be better researched)
                        case 'en_AU':
                        case 'en_GB':
                        case 'en_US':
                        default:
                            switch ($sex2) {

                                case 'M': // I18N: if you need a different number for %d, contact the developers, as a code-change is required
                                    return I18N::translate('great ×%d grandson', $up - 2);
                                case 'F':
                                    return I18N::translate('great ×%d granddaughter', $up - 2);
                                default:
                                    return I18N::translate('great ×%d grandchild', $up - 2);
                            }
                    }
            }
        }
        if (preg_match('/^((?:mot|fat|par)+)(?:bro|sis|sib)((?:son|dau|chi)+)$/', $path, $match)) {
            // cousins in English
            $ascent  = $match[1];
            $descent = $match[2];
            $up      = strlen($ascent) / 3;
            $down    = strlen($descent) / 3;
            $cousin  = min($up, $down); // Moved out of switch (en/default case) so that
            $removed = abs($down - $up); // Spanish (and other languages) can use it, too.

            // Different languages have different rules for naming cousins.  For example,
            // an English “second cousin once removed” is a Polish “cousin of 7th degree”.
            //
            // Need to find out which languages use which rules.
            switch (WT_LOCALE) {
                case 'pl': // Source: Lukasz Wilenski
                    return $this->cousin_name($up + $down + 2, $sex2);
                case 'it':
                    // Source: Michele Locati.  See italian_cousins_names.zip
                    // http://webtrees.net/forums/8-translation/1200-great-xn-grandparent?limit=6&start=6
                    return $this->cousin_name($up + $down - 3, $sex2);
                case 'es':
                    // Source: Wes Groleau.  See http://UniGen.us/Parentesco.html & http://UniGen.us/Parentesco-D.html
                    if ($down == $up) {
                        return $this->cousin_name($cousin, $sex2);
                    } elseif ($down < $up) {
                        return $this->cousin_name2($cousin + 1, $sex2, $this->get_relationship_name_from_path('sib' . $descent, null, null));
                    } else {
                        switch ($sex2) {
                            case 'M':
                                return $this->cousin_name2($cousin + 1, $sex2, $this->get_relationship_name_from_path('bro' . $descent, null, null));
                            case 'F':
                                return $this->cousin_name2($cousin + 1, $sex2, $this->get_relationship_name_from_path('sis' . $descent, null, null));
                            default:
                                return $this->cousin_name2($cousin + 1, $sex2, $this->get_relationship_name_from_path('sib' . $descent, null, null));
                        }
                    }
                case 'en_AU': // See: http://en.wikipedia.org/wiki/File:CousinTree.svg
                case 'en_GB':
                case 'en_US':
                default:
                    switch ($removed) {
                        case 0:
                            return $this->cousin_name($cousin, $sex2);
                        case 1:
                            if ($up > $down) {
                                /* I18N: %s=“fifth cousin”, etc. http://www.ancestry.com/learn/library/article.aspx?article=2856 */
                                return I18N::translate('%s once removed ascending', $this->cousin_name($cousin, $sex2));
                            } else {
                                /* I18N: %s=“fifth cousin”, etc. http://www.ancestry.com/learn/library/article.aspx?article=2856 */
                                return I18N::translate('%s once removed descending', $this->cousin_name($cousin, $sex2));
                            }
                        case 2:
                            if ($up > $down) {
                                /* I18N: %s=“fifth cousin”, etc. */
                                return I18N::translate('%s twice removed ascending', $this->cousin_name($cousin, $sex2));
                            } else {
                                /* I18N: %s=“fifth cousin”, etc. */
                                return I18N::translate('%s twice removed descending', $this->cousin_name($cousin, $sex2));
                            }
                        case 3:
                            if ($up > $down) {
                                /* I18N: %s=“fifth cousin”, etc. */
                                return I18N::translate('%s three times removed ascending', $this->cousin_name($cousin, $sex2));
                            } else {
                                /* I18N: %s=“fifth cousin”, etc. */
                                return I18N::translate('%s three times removed descending', $this->cousin_name($cousin, $sex2));
                            }
                        default:
                            if ($up > $down) {
                                /* I18N: %1$s=“fifth cousin”, etc., %2$d>=4 */
                                return I18N::translate('%1$s %2$d times removed ascending', $this->cousin_name($cousin, $sex2), $removed);
                            } else {
                                /* I18N: %1$s=“fifth cousin”, etc., %2$d>=4 */
                                return I18N::translate('%1$s %2$d times removed descending', $this->cousin_name($cousin, $sex2), $removed);
                            }
                    }
            }
        }

        // Split the relationship into sub-relationships, e.g., third-cousin’s great-uncle.
        // Try splitting at every point, and choose the path with the shorted translated name.

        $relationship = null;
        $path1        = substr($path, 0, 3);
        $path2        = substr($path, 3);
        while ($path2) {
            $tmp = I18N::translate(
            // I18N: A complex relationship, such as “third-cousin’s great-uncle”
                '%1$s’s %2$s',
                $this->get_relationship_name_from_path($path1, null, null), // TODO: need the actual people
                $this->get_relationship_name_from_path($path2, null, null)
            );
            if (!$relationship || strlen($tmp) < strlen($relationship)) {
                $relationship = $tmp;
            }
            $path1 .= substr($path2, 0, 3);
            $path2 = substr($path2, 3);
        }

        return $relationship;
    }

    /**
     * get theme names
     *
     * function to get the names of all of the themes as an array
     * it searches the themes folder and reads the name from the theme_name variable
     * in the theme.php file.
     *
     * @throws \Exception
     *
     * @return string[] An array of theme names and their corresponding folder
     */
    function get_theme_names()
    {
        static $themes;

        if ($themes === null) {
            $themes = array();
            $d      = dir(WT_ROOT . WT_THEMES_DIR);
            while (false !== ($folder = $d->read())) {
                if ($folder[0] !== '.' && $folder[0] !== '_' && is_dir(WT_ROOT . WT_THEMES_DIR . $folder) && file_exists(WT_ROOT . WT_THEMES_DIR . $folder . '/theme.php')) {
                    $themefile = implode('', file(WT_ROOT . WT_THEMES_DIR . $folder . '/theme.php'));
                    if (preg_match('/theme_name\s*=\s*"(.*)";/', $themefile, $match)) {
                        $theme_name = I18N::translate($match[1]);
                        if (array_key_exists($theme_name, $themes)) {
                            throw new \Exception('More than one theme with the same name: ' . $theme_name);
                        } else {
                            $themes[$theme_name] = $folder;
                        }
                    }
                }
            }
            $d->close();
            uksort($themes, __NAMESPACE__ . '\I18N::strcasecmp');
        }

        return $themes;
    }

    /**
     * Function to build an URL querystring from GET variables
     * Optionally, add/replace specified values
     *
     * @param null|string[] $overwrite
     * @param null|string   $separator
     *
     * @return string
     */
    function get_query_url($overwrite = null, $separator = '&')
    {
        if (empty($_GET)) {
            $get = array();
        } else {
            $get = $_GET;
        }
        if (is_array($overwrite)) {
            foreach ($overwrite as $key => $value) {
                $get[$key] = $value;
            }
        }

        $query_string = '';
        foreach ($get as $key => $value) {
            if (!is_array($value)) {
                $query_string .= $separator . rawurlencode($key) . '=' . rawurlencode($value);
            } else {
                foreach ($value as $k => $v) {
                    $query_string .= $separator . rawurlencode($key) . '%5B' . rawurlencode($k) . '%5D=' . rawurlencode($v);
                }
            }
        }
        $query_string = substr($query_string, strlen($separator)); // Remove leading “&amp;”
        if ($query_string) {
            return WT_SCRIPT_NAME . '?' . $query_string;
        } else {
            return WT_SCRIPT_NAME;
        }
    }

    /**
     * Generate a new XREF, unique across all family trees
     *
     * @param string  $type
     * @param integer $ged_id
     *
     * @return string
     */
    function get_new_xref($type = 'INDI', $ged_id = WT_GED_ID)
    {
        /** @var string[] Which tree preference is used for which record type */
        static $type_to_preference = array(
            'INDI' => 'GEDCOM_ID_PREFIX',
            'FAM'  => 'FAM_ID_PREFIX',
            'OBJE' => 'MEDIA_ID_PREFIX',
            'NOTE' => 'NOTE_ID_PREFIX',
            'SOUR' => 'SOURCE_ID_PREFIX',
            'REPO' => 'REPO_ID_PREFIX',
        );

        if (array_key_exists($type, $type_to_preference)) {
            $prefix = Globals::i()->WT_TREE->getPreference($type_to_preference[$type]);
        } else {
            // Use the first non-underscore character
            $prefix = substr(trim($type, '_'), 0, 1);
        }

        do {
            // Use LAST_INSERT_ID(expr) to provide a transaction-safe sequence.  See
            // http://dev.mysql.com/doc/refman/5.6/en/information-functions.html#function_last-insert-id
            $statement = Database::i()->prepare(
                "UPDATE `##next_id` SET next_id = LAST_INSERT_ID(next_id + 1) WHERE record_type = :record_type AND gedcom_id = :gedcom_id"
            );
            $statement->execute(array(
                                    'record_type' => $type,
                                    'gedcom_id'   => $ged_id,
                                ));

            if ($statement->rowCount() === 0) {
                // First time we've used this record type.
                Database::i()->prepare(
                    "INSERT INTO `##next_id` (gedcom_id, record_type, next_id) VALUES(:gedcom_id, :record_type, 1)"
                )
                        ->execute(array(
                                      'record_type' => $type,
                                      'gedcom_id'   => $ged_id,
                                  ));
                $num = 1;
            } else {
                $num = Database::i()->prepare("SELECT LAST_INSERT_ID()")
                               ->fetchOne();
            }

            // Records may already exist with this sequence number.
            $already_used = Database::i()->prepare(
                "SELECT i_id FROM `##individuals` WHERE i_id = :i_id" .
                " UNION ALL " .
                "SELECT f_id FROM `##families` WHERE f_id = :f_id" .
                " UNION ALL " .
                "SELECT s_id FROM `##sources` WHERE s_id = :s_id" .
                " UNION ALL " .
                "SELECT m_id FROM `##media` WHERE m_id = :m_id" .
                " UNION ALL " .
                "SELECT o_id FROM `##other` WHERE o_id = :o_id" .
                " UNION ALL " .
                "SELECT xref FROM `##change` WHERE xref = :xref"
            )
                                    ->execute(array(
                                                  'i_id' => $prefix . $num,
                                                  'f_id' => $prefix . $num,
                                                  's_id' => $prefix . $num,
                                                  'm_id' => $prefix . $num,
                                                  'o_id' => $prefix . $num,
                                                  'xref' => $prefix . $num,
                                              ))
                                    ->fetchOne();
        } while ($already_used);

        return $prefix . $num;
    }

    /**
     * Determines whether the passed in filename is a link to an external source (i.e. contains “://”)
     *
     * @param string $file
     *
     * @return boolean
     */
    function isFileExternal($file)
    {
        return strpos($file, '://') !== false;
    }
}