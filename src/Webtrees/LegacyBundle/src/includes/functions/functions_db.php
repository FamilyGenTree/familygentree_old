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

class FunctionsDbPhp
{
    /**
     * @var FunctionsDbPhp
     */
    protected static $instance;
    /**
     * @var array
     */
    protected $valueStore = array();

    /**
     * Singleton protected
     */
    protected function __construct()
    {

    }

    /**
     * @return FunctionsDbPhp
     */
    public static function i()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Fetch all records linked to a record - when deleting an object, we must
     * also delete all links to it.
     *
     * @param string  $xref
     * @param integer $gedcom_id
     *
     * @return string[]
     */
    function fetch_all_links($xref, $gedcom_id)
    {
        return
            Database::prepare(
                "SELECT l_from FROM `##link` WHERE l_file=? AND l_to=?" .
                " UNION " .
                "SELECT xref FROM `##change` WHERE status='pending' AND gedcom_id=? AND new_gedcom LIKE" .
                " CONCAT('%@', ?, '@%')"
            )
                    ->execute(array(
                                  $gedcom_id,
                                  $xref,
                                  $gedcom_id,
                                  $xref
                              ))
                    ->fetchOneColumn();
    }

    /**
     * Find out if there are any pending changes that a given user may accept.
     *
     * @param User $user
     * @param Tree $tree
     *
     * @return boolean
     */
    function exists_pending_change(User $user = null, Tree $tree = null)
    {
        if ($user === null) {
            $user = Auth::user();
        }

        if ($tree === null) {
            $tree = Globals::i()->WT_TREE;
        }

        if ($user === null || $tree === null) {
            return false;
        }

        return
            $tree->canAcceptChanges($user)
            && Database::prepare(
                "SELECT 1" .
                " FROM `##change`" .
                " WHERE status='pending' AND gedcom_id=?"
            )
                       ->execute(array($tree->getTreeId()))
                       ->fetchOne();
    }

    /**
     * Get a list of all the sources.
     *
     * @param integer $ged_id
     *
     * @return Source[] array
     */
    function get_source_list($ged_id)
    {
        $rows =
            Database::prepare("SELECT s_id AS xref, s_file AS gedcom_id, s_gedcom AS gedcom FROM `##sources` WHERE s_file=?")
                    ->execute(array($ged_id))
                    ->fetchAll();

        $list = array();
        foreach ($rows as $row) {
            $list[] = Source::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
        }
        usort($list, __NAMESPACE__ . '\GedcomRecord::compare');

        return $list;
    }

    /**
     * Get a list of all the repositories.
     *
     * @param integer $ged_id
     *
     * @return Repository[] array
     */
    function get_repo_list($ged_id)
    {
        $rows =
            Database::prepare("SELECT o_id AS xref, o_file AS gedcom_id, o_gedcom AS gedcom FROM `##other` WHERE o_type='REPO' AND o_file=?")
                    ->execute(array($ged_id))
                    ->fetchAll();

        $list = array();
        foreach ($rows as $row) {
            $list[] = Repository::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
        }
        usort($list, __NAMESPACE__ . '\GedcomRecord::compare');

        return $list;
    }

    /**
     * Get a list of all the shared notes.
     *
     * @param integer $ged_id
     *
     * @return Note[] array
     */
    function get_note_list($ged_id)
    {
        $rows =
            Database::prepare("SELECT o_id AS xref, o_file AS gedcom_id, o_gedcom AS gedcom FROM `##other` WHERE o_type='NOTE' AND o_file=?")
                    ->execute(array($ged_id))
                    ->fetchAll();

        $list = array();
        foreach ($rows as $row) {
            $list[] = Note::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
        }
        usort($list, __NAMESPACE__ . '\GedcomRecord::compare');

        return $list;
    }

    /**
     * Search for INDIs using custom SQL generated by the report engine
     *
     * @param string[] $join
     * @param string[] $where
     * @param string[] $order
     *
     * @return Individual[]
     */
    function search_indis_custom($join, $where, $order)
    {
        $sql = "SELECT DISTINCT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom FROM `##individuals` " . implode(' ', $join) . ' WHERE ' . implode(' AND ', $where);
        if ($order) {
            $sql .= ' ORDER BY ' . implode(' ', $order);
        }

        $list = array();
        $rows = Database::prepare($sql)
                        ->fetchAll();
        foreach ($rows as $row) {
            $list[] = Individual::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
        }

        return $list;
    }

    /**
     * Search for FAMs using custom SQL generated by the report engine
     *
     * @param string[] $join
     * @param string[] $where
     * @param string[] $order
     *
     * @return Family[]
     */
    function search_fams_custom($join, $where, $order)
    {
        $sql = "SELECT DISTINCT f_id AS xref, f_file AS gedcom_id, f_gedcom AS gedcom FROM `##families` " . implode(' ', $join) . ' WHERE ' . implode(' AND ', $where);
        if ($order) {
            $sql .= ' ORDER BY ' . implode(' ', $order);
        }

        $list = array();
        $rows = Database::prepare($sql)
                        ->fetchAll();
        foreach ($rows as $row) {
            $list[] = Family::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
        }

        return $list;
    }

    /**
     * Search all individuals
     *
     * @param string[]  $query array of search terms
     * @param integer[] $geds  array of gedcoms to search
     * @param string    $match AND or OR
     *
     * @return Individual[]
     */
    function search_indis($query, $geds, $match)
    {
        // No query => no results
        if (!$query) {
            return array();
        }

        // Convert the query into a SQL expression
        $querysql = array();
        // Convert the query into a regular expression
        $queryregex = array();

        foreach ($query as $q) {
            $queryregex[] = preg_quote(I18N::strtoupper($q), '/');
            $querysql[]   = "i_gedcom LIKE " . Database::quote("%{$q}%") . " COLLATE '" . I18N::$collation . "'";
        }

        $sql = "SELECT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom FROM `##individuals` WHERE (" . implode(" {$match} ", $querysql) . ') AND i_file IN (' . implode(',', $geds) . ')';

        // Group results by gedcom, to minimise switching between privacy files
        $sql .= ' ORDER BY gedcom_id';

        $list = array();
        $rows = Database::prepare($sql)
                        ->fetchAll();
        foreach ($rows as $row) {
            // SQL may have matched on private data or gedcom tags, so check again against privatized data.
            $record = Individual::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
            // Ignore non-genealogical data
            $gedrec = preg_replace('/\n\d (_UID|_WT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcom());
            // Ignore links and tags
            $gedrec = preg_replace('/\n\d ' . WT_REGEX_TAG . '( @' . WT_REGEX_XREF . '@)?/', '', $gedrec);
            // Re-apply the filtering
            $gedrec = I18N::strtoupper($gedrec);
            foreach ($queryregex as $regex) {
                if (!preg_match('/' . $regex . '/', $gedrec)) {
                    continue 2;
                }
            }
            $list[] = $record;
        }

        return $list;
    }

    /**
     * Search the names of individuals
     *
     * @param string[]  $query array of search terms
     * @param integer[] $geds  array of gedcoms to search
     * @param string    $match AND or OR
     *
     * @return Individual[]
     */
    function search_indis_names($query, $geds, $match)
    {
        // No query => no results
        if (!$query) {
            return array();
        }

        // Convert the query into a SQL expression
        $querysql = array();
        foreach ($query as $q) {
            $querysql[] = "n_full LIKE " . Database::quote("%{$q}%") . " COLLATE '" . I18N::$collation . "'";
        }
        $sql = "SELECT DISTINCT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom, n_full FROM `##individuals` JOIN `##name` ON i_id=n_id AND i_file=n_file WHERE (" . implode(" {$match} ", $querysql) . ') AND i_file IN (' . implode(',', $geds) . ')';

        // Group results by gedcom, to minimise switching between privacy files
        $sql .= ' ORDER BY gedcom_id';

        $list = array();
        $rows = Database::prepare($sql)
                        ->fetchAll();
        foreach ($rows as $row) {
            $indi = Individual::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
            // The individual may have private names - and the DB search may have found it.
            if ($indi->canShowName()) {
                foreach ($indi->getAllNames() as $num => $name) {
                    if ($name['fullNN'] == $row->n_full) {
                        $indi->setPrimaryName($num);
                        // We need to clone $indi, as we may have multiple references to the
                        // same person in this list, and the "primary name" would otherwise
                        // be shared amongst all of them.
                        $list[] = clone $indi;
                        // Only need to match an individual on one name
                        break;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Search for individuals names/places using soundex
     *
     * @param string    $soundex
     * @param string    $lastname
     * @param string    $firstname
     * @param string    $place
     * @param integer[] $geds
     *
     * @return Individual[]
     */
    function search_indis_soundex($soundex, $lastname, $firstname, $place, $geds)
    {
        $sql = "SELECT DISTINCT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom FROM `##individuals`";
        if ($place) {
            $sql .= " JOIN `##placelinks` ON (pl_file=i_file AND pl_gid=i_id)";
            $sql .= " JOIN `##places` ON (p_file=pl_file AND pl_p_id=p_id)";
        }
        if ($firstname || $lastname) {
            $sql .= " JOIN `##name` ON (i_file=n_file AND i_id=n_id)";
        }
        $sql .= ' WHERE i_file IN (' . implode(',', $geds) . ')';
        switch ($soundex) {
            case 'Russell':
                $givn_sdx = Soundex::russell($firstname);
                $surn_sdx = Soundex::russell($lastname);
                $plac_sdx = Soundex::russell($place);
                $field    = 'std';
                break;
            case 'DaitchM':
                $givn_sdx = Soundex::daitchMokotoff($firstname);
                $surn_sdx = Soundex::daitchMokotoff($lastname);
                $plac_sdx = Soundex::daitchMokotoff($place);
                $field    = 'dm';
                break;
            default:
                throw new \InvalidArgumentException('soundex: ' . $soundex);
        }

        // Nothing to search for?  Return nothing.
        if (!$givn_sdx && !$surn_sdx && !$plac_sdx) {
            return array();
        }

        $sql_args = array();
        if ($firstname && $givn_sdx) {
            $givn_sdx = explode(':', $givn_sdx);
            foreach ($givn_sdx as $k => $v) {
                $givn_sdx[$k] = "n_soundex_givn_{$field} LIKE CONCAT('%', ?, '%')";
                $sql_args[]   = $v;
            }
            $sql .= ' AND (' . implode(' OR ', $givn_sdx) . ')';
        }
        if ($lastname && $surn_sdx) {
            $surn_sdx = explode(':', $surn_sdx);
            foreach ($surn_sdx as $k => $v) {
                $surn_sdx[$k] = "n_soundex_surn_{$field} LIKE CONCAT('%', ?, '%')";
                $sql_args[]   = $v;
            }
            $sql .= ' AND (' . implode(' OR ', $surn_sdx) . ')';
        }
        if ($place && $plac_sdx) {
            $plac_sdx = explode(':', $plac_sdx);
            foreach ($plac_sdx as $k => $v) {
                $plac_sdx[$k] = "p_{$field}_soundex LIKE CONCAT('%', ?, '%')";
                $sql_args[]   = $v;
            }
            $sql .= ' AND (' . implode(' OR ', $plac_sdx) . ')';
        }

        // Group results by gedcom, to minimise switching between privacy files
        $sql .= ' ORDER BY gedcom_id';

        $list = array();
        $rows = Database::prepare($sql)
                        ->execute($sql_args)
                        ->fetchAll();
        foreach ($rows as $row) {
            $indi = Individual::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
            if ($indi->canShowName()) {
                $list[] = $indi;
            }
        }

        return $list;
    }

    /**
     * get recent changes since the given julian day inclusive
     *
     * @param integer $jd leave empty to include all
     * @param boolean $allgeds
     *
     * @return string[] List of XREFs of records with changes
     */
    function get_recent_changes($jd = 0, $allgeds = false)
    {
        $sql  = "SELECT d_gid FROM `##dates` WHERE d_fact='CHAN' AND d_julianday1>=?";
        $vars = array($jd);
        if (!$allgeds) {
            $sql .= " AND d_file=?";
            $vars[] = WT_GED_ID;
        }
        $sql .= " ORDER BY d_julianday1 DESC";

        return Database::prepare($sql)
                       ->execute($vars)
                       ->fetchOneColumn();
    }

    /**
     * Seach for individuals with events on a given day.
     *
     * @param integer $day
     * @param integer $month
     * @param integer $year
     * @param string  $facts
     *
     * @return Individual[]
     */
    function search_indis_dates($day, $month, $year, $facts)
    {
        $sql  = "SELECT DISTINCT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom FROM `##individuals` JOIN `##dates` ON i_id=d_gid AND i_file=d_file WHERE i_file=?";
        $vars = array(WT_GED_ID);
        if ($day) {
            $sql .= " AND d_day=?";
            $vars[] = $day;
        }
        if ($month) {
            $sql .= " AND d_month=?";
            $vars[] = $month;
        }
        if ($year) {
            $sql .= " AND d_year=?";
            $vars[] = $year;
        }
        if ($facts) {
            $facts = preg_split('/[, ;]+/', $facts);
            foreach ($facts as $key => $value) {
                if ($value[0] == '!') {
                    $facts[$key] = "d_fact!=?";
                    $vars[]      = substr($value, 1);
                } else {
                    $facts[$key] = "d_fact=?";
                    $vars[]      = $value;
                }
            }
            $sql .= ' AND ' . implode(' AND ', $facts);
        }

        $list = array();
        $rows = Database::prepare($sql)
                        ->execute($vars)
                        ->fetchAll();
        foreach ($rows as $row) {
            $list[] = Individual::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
        }

        return $list;
    }

    /**
     * Search family records
     *
     * @param string[]  $query array of search terms
     * @param integer[] $geds  array of gedcoms to search
     * @param string    $match AND or OR
     *
     * @return Family[]
     */
    function search_fams($query, $geds, $match)
    {
        // No query => no results
        if (!$query) {
            return array();
        }

        // Convert the query into a SQL expression
        $querysql = array();
        // Convert the query into a regular expression
        $queryregex = array();

        foreach ($query as $q) {
            $queryregex[] = preg_quote(I18N::strtoupper($q), '/');
            $querysql[]   = "f_gedcom LIKE " . Database::quote("%{$q}%") . " COLLATE '" . I18N::$collation . "'";
        }

        $sql = "SELECT f_id AS xref, f_file AS gedcom_id, f_gedcom AS gedcom FROM `##families` WHERE (" . implode(" {$match} ", $querysql) . ') AND f_file IN (' . implode(',', $geds) . ')';

        // Group results by gedcom, to minimise switching between privacy files
        $sql .= ' ORDER BY gedcom_id';

        $list = array();
        $rows = Database::prepare($sql)
                        ->fetchAll();
        foreach ($rows as $row) {
            // SQL may have matched on private data or gedcom tags, so check again against privatized data.
            $record = Family::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
            // Ignore non-genealogical data
            $gedrec = preg_replace('/\n\d (_UID|_WT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcom());
            // Ignore links and tags
            $gedrec = preg_replace('/\n\d ' . WT_REGEX_TAG . '( @' . WT_REGEX_XREF . '@)?/', '', $gedrec);
            // Ignore tags
            $gedrec = preg_replace('/\n\d ' . WT_REGEX_TAG . ' ?/', '', $gedrec);
            // Re-apply the filtering
            $gedrec = I18N::strtoupper($gedrec);
            foreach ($queryregex as $regex) {
                if (!preg_match('/' . $regex . '/', $gedrec)) {
                    continue 2;
                }
            }
            $list[] = $record;
        }

        return $list;
    }

    /**
     * Search the names of the husb/wife in a family
     *
     * @param string[]  $query array of search terms
     * @param integer[] $geds  array of gedcoms to search
     * @param string    $match AND or OR
     *
     * @return Family[]
     */
    function search_fams_names($query, $geds, $match)
    {
        // No query => no results
        if (!$query) {
            return array();
        }

        // Convert the query into a SQL expression
        $querysql = array();
        foreach ($query as $q) {
            $querysql[] = "(husb.n_full LIKE " . Database::quote("%{$q}%") . " COLLATE '" . I18N::$collation . "' OR wife.n_full LIKE " . Database::quote("%{$q}%") . " COLLATE '" . I18N::$collation . "')";
        }

        $sql = "SELECT DISTINCT f_id AS xref, f_file AS gedcom_id, f_gedcom AS gedcom FROM `##families` LEFT OUTER JOIN `##name` husb ON f_husb=husb.n_id AND f_file=husb.n_file LEFT OUTER JOIN `##name` wife ON f_wife=wife.n_id AND f_file=wife.n_file WHERE (" . implode(" {$match} ", $querysql) . ') AND f_file IN (' . implode(',', $geds) . ')';

        // Group results by gedcom, to minimise switching between privacy files
        $sql .= ' ORDER BY gedcom_id';

        $list = array();
        $rows = Database::prepare($sql)
                        ->fetchAll();
        foreach ($rows as $row) {
            $indi = Family::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
            if ($indi->canShowName()) {
                $list[] = $indi;
            }
        }

        return $list;
    }

    /**
     * Search the gedcom records of sources
     *
     * @param string[]  $query array of search terms
     * @param integer[] $geds  array of gedcoms to search
     * @param string    $match AND or OR
     *
     * @return Source[]
     */
    function search_sources($query, $geds, $match)
    {
        // No query => no results
        if (!$query) {
            return array();
        }

        // Convert the query into a SQL expression
        $querysql = array();
        // Convert the query into a regular expression
        $queryregex = array();

        foreach ($query as $q) {
            $queryregex[] = preg_quote(I18N::strtoupper($q), '/');
            $querysql[]   = "s_gedcom LIKE " . Database::quote("%{$q}%") . " COLLATE '" . I18N::$collation . "'";
        }

        $sql = "SELECT s_id AS xref, s_file AS gedcom_id, s_gedcom AS gedcom FROM `##sources` WHERE (" . implode(" {$match} ", $querysql) . ') AND s_file IN (' . implode(',', $geds) . ')';

        // Group results by gedcom, to minimise switching between privacy files
        $sql .= ' ORDER BY gedcom_id';

        $list = array();
        $rows = Database::prepare($sql)
                        ->fetchAll();
        foreach ($rows as $row) {
            // SQL may have matched on private data or gedcom tags, so check again against privatized data.
            $record = Source::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
            // Ignore non-genealogical data
            $gedrec = preg_replace('/\n\d (_UID|_WT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcom());
            // Ignore links and tags
            $gedrec = preg_replace('/\n\d ' . WT_REGEX_TAG . '( @' . WT_REGEX_XREF . '@)?/', '', $gedrec);
            // Ignore tags
            $gedrec = preg_replace('/\n\d ' . WT_REGEX_TAG . ' ?/', '', $gedrec);
            // Re-apply the filtering
            $gedrec = I18N::strtoupper($gedrec);
            foreach ($queryregex as $regex) {
                if (!preg_match('/' . $regex . '/', $gedrec)) {
                    continue 2;
                }
            }
            $list[] = $record;
        }

        return $list;
    }

    /**
     * Search the shared notes
     *
     * @param string[]  $query array of search terms
     * @param integer[] $geds  array of gedcoms to search
     * @param string    $match AND or OR
     *
     * @return Note[]
     */
    function search_notes($query, $geds, $match)
    {
        // No query => no results
        if (!$query) {
            return array();
        }

        // Convert the query into a SQL expression
        $querysql = array();
        // Convert the query into a regular expression
        $queryregex = array();

        foreach ($query as $q) {
            $queryregex[] = preg_quote(I18N::strtoupper($q), '/');
            $querysql[]   = "o_gedcom LIKE " . Database::quote("%{$q}%") . " COLLATE '" . I18N::$collation . "'";
        }

        $sql = "SELECT o_id AS xref, o_file AS gedcom_id, o_gedcom AS gedcom FROM `##other` WHERE (" . implode(" {$match} ", $querysql) . ") AND o_type='NOTE' AND o_file IN (" . implode(',', $geds) . ')';

        // Group results by gedcom, to minimise switching between privacy files
        $sql .= ' ORDER BY gedcom_id';

        $list = array();
        $rows = Database::prepare($sql)
                        ->fetchAll();
        foreach ($rows as $row) {
            // SQL may have matched on private data or gedcom tags, so check again against privatized data.
            $record = Note::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
            // Ignore non-genealogical data
            $gedrec = preg_replace('/\n\d (_UID|_WT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcom());
            // Ignore links and tags
            $gedrec = preg_replace('/\n\d ' . WT_REGEX_TAG . '( @' . WT_REGEX_XREF . '@)?/', '', $gedrec);
            // Ignore tags
            $gedrec = preg_replace('/\n\d ' . WT_REGEX_TAG . ' ?/', '', $gedrec);
            // Re-apply the filtering
            $gedrec = I18N::strtoupper($gedrec);
            foreach ($queryregex as $regex) {
                if (!preg_match('/' . $regex . '/', $gedrec)) {
                    continue 2;
                }
            }
            $list[] = $record;
        }

        return $list;
    }


    /**
     * Search the gedcom records of repositories
     *
     * @param string[]  $query array of search terms
     * @param integer[] $geds  array of gedcoms to search
     * @param string    $match AND or OR
     *
     * @return Repository[]
     */
    function search_repos($query, $geds, $match)
    {
        // No query => no results
        if (!$query) {
            return array();
        }

        // Convert the query into a SQL expression
        $querysql = array();
        // Convert the query into a regular expression
        $queryregex = array();

        foreach ($query as $q) {
            $queryregex[] = preg_quote(I18N::strtoupper($q), '/');
            $querysql[]   = "o_gedcom LIKE " . Database::quote("%{$q}%") . " COLLATE '" . I18N::$collation . "'";
        }

        $sql = "SELECT o_id AS xref, o_file AS gedcom_id, o_gedcom AS gedcom FROM `##other` WHERE (" . implode(" {$match} ", $querysql) . ") AND o_type='REPO' AND o_file IN (" . implode(',', $geds) . ')';

        // Group results by gedcom, to minimise switching between privacy files
        $sql .= ' ORDER BY gedcom_id';

        $list = array();
        $rows = Database::prepare($sql)
                        ->fetchAll();
        foreach ($rows as $row) {
            // SQL may have matched on private data or gedcom tags, so check again against privatized data.
            $record = Repository::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
            // Ignore non-genealogical data
            $gedrec = preg_replace('/\n\d (_UID|_WT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcom());
            // Ignore links and tags
            $gedrec = preg_replace('/\n\d ' . WT_REGEX_TAG . '( @' . WT_REGEX_XREF . '@)?/', '', $gedrec);
            // Ignore tags
            $gedrec = preg_replace('/\n\d ' . WT_REGEX_TAG . ' ?/', '', $gedrec);
            // Re-apply the filtering
            $gedrec = I18N::strtoupper($gedrec);
            foreach ($queryregex as $regex) {
                if (!preg_match('/' . $regex . '/', $gedrec)) {
                    continue 2;
                }
            }
            $list[] = $record;
        }

        return $list;
    }

    /**
     * Find the record for the given rin.
     *
     * @param string $rin
     *
     * @return string
     */
    function find_rin_id($rin)
    {
        $xref =
            Database::prepare("SELECT i_id FROM `##individuals` WHERE i_rin=? AND i_file=?")
                    ->execute(array(
                                  $rin,
                                  WT_GED_ID
                              ))
                    ->fetchOne();

        return $xref ? $xref : $rin;
    }

    /**
     * Get array of common surnames
     *
     * This function returns a simple array of the most common surnames
     * found in the individuals list.
     *
     * @param integer $min the number of times a surname must occur before it is added to the array
     *
     * @return mixed[][]
     */
    function get_common_surnames($min)
    {
        $COMMON_NAMES_ADD    = Globals::i()->WT_TREE->getPreference('COMMON_NAMES_ADD');
        $COMMON_NAMES_REMOVE = Globals::i()->WT_TREE->getPreference('COMMON_NAMES_REMOVE');

        $topsurns = $this->get_top_surnames(WT_GED_ID, $min, 0);
        foreach (explode(',', $COMMON_NAMES_ADD) as $surname) {
            if ($surname && !array_key_exists($surname, $topsurns)) {
                $topsurns[$surname] = $min;
            }
        }
        foreach (explode(',', $COMMON_NAMES_REMOVE) as $surname) {
            unset($topsurns[I18N::strtoupper($surname)]);
        }

        //-- check if we found some, else recurse
        if (empty($topsurns) && $min > 2) {
            return $this->get_common_surnames($min / 2);
        } else {
            uksort($topsurns, __NAMESPACE__ . '\I18N::strcasecmp');
            foreach ($topsurns as $key => $value) {
                $topsurns[$key] = array(
                    'name'  => $key,
                    'match' => $value
                );
            }

            return $topsurns;
        }
    }

    /**
     * get the top surnames
     *
     * @param integer $ged_id fetch surnames from this gedcom
     * @param integer $min    only fetch surnames occuring this many times
     * @param integer $max    only fetch this number of surnames (0=all)
     *
     * @return string[]
     */
    function get_top_surnames($ged_id, $min, $max)
    {
        // Use n_surn, rather than n_surname, as it is used to generate URLs for
        // the indi-list, etc.
        $max = (int)$max;
        if ($max == 0) {
            return
                Database::prepare(
                    "SELECT SQL_CACHE n_surn, COUNT(n_surn) FROM `##name`" .
                    " WHERE n_file = :tree_id AND n_type != '_MARNM' AND n_surn NOT IN ('@N.N.', '', '?', 'UNKNOWN')" .
                    " GROUP BY n_surn HAVING COUNT(n_surn) >= :min" .
                    " ORDER BY 2 DESC"
                )
                        ->execute(array(
                                      'tree_id' => $ged_id,
                                      'min'     => $min,
                                  ))
                        ->fetchAssoc();
        } else {
            return
                Database::prepare(
                    "SELECT SQL_CACHE n_surn, COUNT(n_surn) FROM `##name`" .
                    " WHERE n_file = :tree_id AND n_type != '_MARNM' AND n_surn NOT IN ('@N.N.', '', '?', 'UNKNOWN')" .
                    " GROUP BY n_surn HAVING COUNT(n_surn) >= :min" .
                    " ORDER BY 2 DESC" .
                    " LIMIT :limit"
                )
                        ->execute(array(
                                      'tree_id' => $ged_id,
                                      'min'     => $min,
                                      'limit'   => $max,
                                  ))
                        ->fetchAssoc();
        }
    }

    /**
     * Get a list of events whose anniversary occured on a given julian day.
     * Used on the on-this-day/upcoming blocks and the day/month calendar views.
     *
     * @param integer $jd     the julian day
     * @param string  $facts  restrict the search to just these facts or leave blank for all
     * @param integer $ged_id the id of the gedcom to search
     *
     * @return Fact[]
     */
    function get_anniversary_events($jd, $facts = '', $ged_id = WT_GED_ID)
    {
        // If no facts specified, get all except these
        $skipfacts = "CHAN,BAPL,SLGC,SLGS,ENDL,CENS,RESI,NOTE,ADDR,OBJE,SOUR,PAGE,DATA,TEXT";
        if ($facts != '_TODO') {
            $skipfacts .= ',_TODO';
        }

        $found_facts = array();
        foreach (array(
                     new GregorianDate($jd),
                     new JulianDate($jd),
                     new FrenchDate($jd),
                     new JewishDate($jd),
                     new HijriDate($jd),
                     new JalaliDate($jd),
                 ) as $anniv) {
            // Build a SQL where clause to match anniversaries in the appropriate calendar.
            $where = "WHERE d_type='" . $anniv->Format('%@') . "'";
            // SIMPLE CASES:
            // a) Non-hebrew anniversaries
            // b) Hebrew months TVT, SHV, IYR, SVN, TMZ, AAV, ELL
            if (!$anniv instanceof JewishDate
                || in_array($anniv->m, array(
                    1,
                    5,
                    6,
                    9,
                    10,
                    11,
                    12,
                    13
                ))
            ) {
                // Dates without days go on the first day of the month
                // Dates with invalid days go on the last day of the month
                if ($anniv->d == 1) {
                    $where .= " AND d_day<=1";
                } else if ($anniv->d == $anniv->daysInMonth()) {
                    $where .= " AND d_day>={$anniv->d}";
                } else {
                    $where .= " AND d_day={$anniv->d}";
                }
                $where .= " AND d_mon={$anniv->m}";
            } else {
                // SPECIAL CASES:
                switch ($anniv->m) {
                    case 2:
                        // 29 CSH does not include 30 CSH (but would include an invalid 31 CSH if there were no 30 CSH)
                        if ($anniv->d == 1) {
                            $where .= " AND d_day<=1 AND d_mon=2";
                        } elseif ($anniv->d == 30) {
                            $where .= " AND d_day>=30 AND d_mon=2";
                        } elseif ($anniv->d == 29 && $anniv->daysInMonth() == 29) {
                            $where .= " AND (d_day=29 OR d_day>30) AND d_mon=2";
                        } else {
                            $where .= " AND d_day={$anniv->d} AND d_mon=2";
                        }
                        break;
                    case 3:
                        // 1 KSL includes 30 CSH (if this year didn’t have 30 CSH)
                        // 29 KSL does not include 30 KSL (but would include an invalid 31 KSL if there were no 30 KSL)
                        if ($anniv->d == 1) {
                            $tmp = new JewishDate(array(
                                                      $anniv->y,
                                                      'CSH',
                                                      1
                                                  ));
                            if ($tmp->daysInMonth() == 29) {
                                $where .= " AND (d_day<=1 AND d_mon=3 OR d_day=30 AND d_mon=2)";
                            } else {
                                $where .= " AND d_day<=1 AND d_mon=3";
                            }
                        } else if ($anniv->d == 30) {
                            $where .= " AND d_day>=30 AND d_mon=3";
                        } elseif ($anniv->d == 29 && $anniv->daysInMonth() == 29) {
                            $where .= " AND (d_day=29 OR d_day>30) AND d_mon=3";
                        } else {
                            $where .= " AND d_day={$anniv->d} AND d_mon=3";
                        }
                        break;
                    case 4:
                        // 1 TVT includes 30 KSL (if this year didn’t have 30 KSL)
                        if ($anniv->d == 1) {
                            $tmp = new JewishDate(array(
                                                      $anniv->y,
                                                      'KSL',
                                                      1
                                                  ));
                            if ($tmp->daysInMonth() == 29) {
                                $where .= " AND (d_day<=1 AND d_mon=4 OR d_day=30 AND d_mon=3)";
                            } else {
                                $where .= " AND d_day<=1 AND d_mon=4";
                            }
                        } else if ($anniv->d == $anniv->daysInMonth()) {
                            $where .= " AND d_day>={$anniv->d} AND d_mon=4";
                        } else {
                            $where .= " AND d_day={$anniv->d} AND d_mon=4";
                        }
                        break;
                    case 7: // ADS includes ADR (non-leap)
                        if ($anniv->d == 1) {
                            $where .= " AND d_day<=1";
                        } elseif ($anniv->d == $anniv->daysInMonth()) {
                            $where .= " AND d_day>={$anniv->d}";
                        } else {
                            $where .= " AND d_day={$anniv->d}";
                        }
                        $where .= " AND (d_mon=6 AND MOD(7*d_year+1, 19)>=7 OR d_mon=7)";
                        break;
                    case 8: // 1 NSN includes 30 ADR, if this year is non-leap
                        if ($anniv->d == 1) {
                            if ($anniv->isLeapYear()) {
                                $where .= " AND d_day<=1 AND d_mon=8";
                            } else {
                                $where .= " AND (d_day<=1 AND d_mon=8 OR d_day=30 AND d_mon=6)";
                            }
                        } elseif ($anniv->d == $anniv->daysInMonth()) {
                            $where .= " AND d_day>={$anniv->d} AND d_mon=8";
                        } else {
                            $where .= " AND d_day={$anniv->d} AND d_mon=8";
                        }
                        break;
                }
            }
            // Only events in the past (includes dates without a year)
            $where .= " AND d_year<={$anniv->y}";
            // Restrict to certain types of fact
            if (empty($facts)) {
                $excl_facts = "'" . preg_replace('/\W+/', "','", $skipfacts) . "'";
                $where .= " AND d_fact NOT IN ({$excl_facts})";
            } else {
                $incl_facts = "'" . preg_replace('/\W+/', "','", $facts) . "'";
                $where .= " AND d_fact IN ({$incl_facts})";
            }
            // Only get events from the current gedcom
            $where .= " AND d_file=" . $ged_id;

            // Now fetch these anniversaries
            $ind_sql = "SELECT DISTINCT 'INDI' AS type, i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom, d_type, d_day, d_month, d_year, d_fact FROM `##dates`, `##individuals` {$where} AND d_gid=i_id AND d_file=i_file ORDER BY d_day ASC, d_year DESC";
            $fam_sql = "SELECT DISTINCT 'FAM'  AS type, f_id AS xref, f_file AS gedcom_id, f_gedcom AS gedcom, d_type, d_day, d_month, d_year, d_fact FROM `##dates`, `##families` {$where} AND d_gid=f_id AND d_file=f_file ORDER BY d_day ASC, d_year DESC";
            foreach (array(
                         $ind_sql,
                         $fam_sql
                     ) as $sql) {
                $rows = Database::prepare($sql)
                                ->fetchAll();
                foreach ($rows as $row) {
                    if ($row->type == 'INDI') {
                        $record = Individual::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
                    } else {
                        $record = Family::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
                    }
                    $anniv_date = new Date($row->d_type . ' ' . $row->d_day . ' ' . $row->d_month . ' ' . $row->d_year);
                    foreach ($record->getFacts(str_replace(' ', '|', $facts)) as $fact) {
                        if (($fact->getDate()
                                  ->MinDate() == $anniv_date->MinDate()
                             || $fact->getDate()
                                     ->MaxDate() == $anniv_date->MinDate())
                            && $fact->getTag() === $row->d_fact
                        ) {
                            $fact->anniv   = $row->d_year === 0 ? 0 : $anniv->y - $row->d_year;
                            $found_facts[] = $fact;
                        }
                    }
                }
            }
        }

        return $found_facts;
    }

    /**
     * Get a list of events which occured during a given date range.
     *
     * @param integer $jd1    the start range of julian day
     * @param integer $jd2    the end range of julian day
     * @param string  $facts  restrict the search to just these facts or leave blank for all
     * @param integer $ged_id the id of the gedcom to search
     *
     * @return Fact[]
     */
    function get_calendar_events($jd1, $jd2, $facts = '', $ged_id = WT_GED_ID)
    {
        // If no facts specified, get all except these
        $skipfacts = "CHAN,BAPL,SLGC,SLGS,ENDL,CENS,RESI,NOTE,ADDR,OBJE,SOUR,PAGE,DATA,TEXT";
        if ($facts != '_TODO') {
            $skipfacts .= ',_TODO';
        }

        $found_facts = array();

        // Events that start or end during the period
        $where = "WHERE (d_julianday1>={$jd1} AND d_julianday1<={$jd2} OR d_julianday2>={$jd1} AND d_julianday2<={$jd2})";

        // Restrict to certain types of fact
        if (empty($facts)) {
            $excl_facts = "'" . preg_replace('/\W+/', "','", $skipfacts) . "'";
            $where .= " AND d_fact NOT IN ({$excl_facts})";
        } else {
            $incl_facts = "'" . preg_replace('/\W+/', "','", $facts) . "'";
            $where .= " AND d_fact IN ({$incl_facts})";
        }
        // Only get events from the current gedcom
        $where .= " AND d_file=" . $ged_id;

        // Now fetch these events
        $ind_sql = "SELECT d_gid AS xref, i_file AS gedcom_id, i_gedcom AS gedcom, 'INDI' AS type, d_type, d_day, d_month, d_year, d_fact, d_type FROM `##dates`, `##individuals` {$where} AND d_gid=i_id AND d_file=i_file GROUP BY d_julianday1, d_gid ORDER BY d_julianday1";
        $fam_sql = "SELECT d_gid AS xref, f_file AS gedcom_id, f_gedcom AS gedcom, 'FAM'  AS type, d_type, d_day, d_month, d_year, d_fact, d_type FROM `##dates`, `##families`    {$where} AND d_gid=f_id AND d_file=f_file GROUP BY d_julianday1, d_gid ORDER BY d_julianday1";
        foreach (array(
                     $ind_sql,
                     $fam_sql
                 ) as $sql) {
            $rows = Database::prepare($sql)
                            ->fetchAll();
            foreach ($rows as $row) {
                if ($row->type == 'INDI') {
                    $record = Individual::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
                } else {
                    $record = Family::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
                }
                $anniv_date = new Date($row->d_type . ' ' . $row->d_day . ' ' . $row->d_month . ' ' . $row->d_year);
                foreach ($record->getFacts(str_replace(' ', '|', $facts)) as $fact) {
                    if ($fact->getDate() == $anniv_date) {
                        $fact->anniv   = 0;
                        $found_facts[] = $fact;
                    }
                }
            }
        }

        return $found_facts;
    }

    /**
     * Get the list of current and upcoming events, sorted by anniversary date
     *
     * @param integer $jd1
     * @param integer $jd2
     * @param string  $events
     *
     * @return Fact[]
     */
    function get_events_list($jd1, $jd2, $events = '')
    {
        $found_facts = array();
        for ($jd = $jd1; $jd <= $jd2; ++$jd) {
            $found_facts = array_merge($found_facts, FunctionsDbPhp::i()->get_anniversary_events($jd, $events));
        }

        return $found_facts;
    }

////////////////////////////////////////////////////////////////////////////////
//
////////////////////////////////////////////////////////////////////////////////

    /**
     * Check if a media file is shared (i.e. used by another gedcom)
     *
     * @param string  $file_name
     * @param integer $ged_id
     *
     * @return boolean
     */
    function is_media_used_in_other_gedcom($file_name, $ged_id)
    {
        return
            (bool)Database::prepare("SELECT COUNT(*) FROM `##media` WHERE m_filename LIKE ? AND m_file<>?")
                          ->execute(array(
                                        "%{$file_name}",
                                        $ged_id
                                    ))
                          ->fetchOne();
    }

    /**
     * @param $ged_id
     *
     * @return null|string
     */
    function get_gedcom_from_id($ged_id)
    {
        // No need to look up the default gedcom
        if (defined('WT_GED_ID') && defined('WT_GEDCOM') && $ged_id == WT_GED_ID) {
            return WT_GEDCOM;
        }

        return
            Database::prepare("SELECT SQL_CACHE gedcom_name FROM `##gedcom` WHERE gedcom_id=?")
                    ->execute(array($ged_id))
                    ->fetchOne();
    }

    /**
     * Convert an (external) gedcom name to an (internal) gedcom ID.
     *
     * @param string $ged_name
     *
     * @return integer|null
     */
    function get_id_from_gedcom($ged_name)
    {
        // No need to look up the default gedcom
        if (defined('WT_GED_ID') && defined('WT_GEDCOM') && $ged_name == WT_GEDCOM) {
            return WT_GED_ID;
        }

        return
            Database::prepare("SELECT SQL_CACHE gedcom_id FROM `##gedcom` WHERE gedcom_name=?")
                    ->execute(array($ged_name))
                    ->fetchOne();
    }

    /**
     * @param integer $user_id
     *
     * @return string[][]
     */
    function get_user_blocks($user_id)
    {
        $blocks = array(
            'main' => array(),
            'side' => array()
        );
        $rows   = Database::prepare(
            "SELECT SQL_CACHE location, block_id, module_name" .
            " FROM  `##block`" .
            " JOIN  `##module` USING (module_name)" .
            " JOIN  `##module_privacy` USING (module_name)" .
            " WHERE user_id=?" .
            " AND   status='enabled'" .
            " AND   `##module_privacy`.gedcom_id=?" .
            " AND   access_level>=?" .
            " ORDER BY location, block_order"
        )
                          ->execute(array(
                                        $user_id,
                                        WT_GED_ID,
                                        WT_USER_ACCESS_LEVEL
                                    ))
                          ->fetchAll();
        foreach ($rows as $row) {
            $blocks[$row->location][$row->block_id] = $row->module_name;
        }

        return $blocks;
    }

    /**
     * NOTE - this function is only correct when $gedcom_id==WT_GED_ID
     * since the privacy depends on WT_USER_ACCESS_LEVEL, which depends
     * on WT_GED_ID        "SELECT SQL_CACHE location, block_id, module_name".
     *
     * @param integer $gedcom_id
     *
     * @return string[][]
     */
    function get_gedcom_blocks($gedcom_id)
    {
        $blocks = array(
            'main' => array(),
            'side' => array()
        );
        $rows   = Database::prepare(
            "SELECT SQL_CACHE location, block_id, module_name" .
            " FROM  `##block`" .
            " JOIN  `##module` USING (module_name)" .
            " JOIN  `##module_privacy` USING (module_name, gedcom_id)" .
            " WHERE gedcom_id=?" .
            " AND   status='enabled'" .
            " AND   access_level>=?" .
            " ORDER BY location, block_order"
        )
                          ->execute(array(
                                        $gedcom_id,
                                        WT_USER_ACCESS_LEVEL
                                    ))
                          ->fetchAll();
        foreach ($rows as $row) {
            $blocks[$row->location][$row->block_id] = $row->module_name;
        }

        return $blocks;
    }

    /**
     * @param integer     $block_id
     * @param string      $setting_name
     * @param string|null $default_value
     *
     * @return null|string
     */
    function get_block_setting($block_id, $setting_name, $default_value = null)
    {
        static $statement;
        if ($statement === null) {
            $statement = Database::prepare(
                "SELECT SQL_CACHE setting_value FROM `##block_setting` WHERE block_id=? AND setting_name=?"
            );
        }
        $setting_value = $statement->execute(array(
                                                 $block_id,
                                                 $setting_name
                                             ))
                                   ->fetchOne();

        return $setting_value === null ? $default_value : $setting_value;
    }

    /**
     * @param integer     $block_id
     * @param string      $setting_name
     * @param string|null $setting_value
     *
     * @throws \Exception
     */
    function set_block_setting($block_id, $setting_name, $setting_value)
    {
        if ($setting_value === null) {
            Database::prepare("DELETE FROM `##block_setting` WHERE block_id=? AND setting_name=?")
                    ->execute(array(
                                  $block_id,
                                  $setting_name
                              ));
        } else {
            Database::prepare("REPLACE INTO `##block_setting` (block_id, setting_name, setting_value) VALUES (?, ?, ?)")
                    ->execute(array(
                                  $block_id,
                                  $setting_name,
                                  $setting_value
                              ));
        }
    }

    /**
     * Update favorites after merging records.
     *
     * @param string  $xref_from
     * @param string  $xref_to
     * @param integer $ged_id
     *
     * @return integer
     */
    function update_favorites($xref_from, $xref_to, $ged_id = WT_GED_ID)
    {
        return
            Database::prepare("UPDATE `##favorite` SET xref=? WHERE xref=? AND gedcom_id=?")
                    ->execute(array(
                                  $xref_to,
                                  $xref_from,
                                  $ged_id
                              ))
                    ->rowCount();
    }
}