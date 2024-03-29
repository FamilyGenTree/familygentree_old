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
 * Class BatchUpdateBasePlugin
 *
 * Each plugin should extend this class, and implement these two functions:
 *
 * bool doesRecordNeedUpdate($xref, $gedrec)
 * string updateRecord($xref, $gedrec)
 */
class BatchUpdateBasePlugin
{
    public $chan = false; // User option; update change record

    /**
     * Default is to operate on INDI records
     *
     * @return string[]
     */
    function getRecordTypesToUpdate()
    {
        return array('INDI');
    }

    /**
     * Default option is just the "don't update CHAN record"
     */
    function getOptions()
    {
        $this->chan = Filter::getBool('chan');
    }

    /**
     * Default option is just the "don't update CHAN record"
     *
     * @return string
     */
    function getOptionsForm()
    {
        return
            '<tr><th>' . I18N::translate('Do not update the “last change” record') . '</th>' .
            '<td><select name="chan" onchange="this.form.submit();">' .
            '<option value="0" ' . ($this->chan ? '' : 'selected') . '>' . I18N::translate('yes') . '</option>' .
            '<option value="1" ' . ($this->chan ? 'selected' : '') . '>' . I18N::translate('no') . '</option>' .
            '</select></td></tr>';
    }

    /**
     * Default buttons are update and update_all
     *
     * @param string $xref
     *
     * @return string[]
     */
    function getActionButtons($xref)
    {
        if (Auth::user()
                ->getPreference('auto_accept')
        ) {
            return array(
                batch_update_WT_Module::createSubmitButton(I18N::translate('Update'), $xref, 'update'),
                batch_update_WT_Module::createSubmitButton(I18N::translate('Update all'), $xref, 'update_all')
            );
        } else {
            return array(
                batch_update_WT_Module::createSubmitButton(I18N::translate('Update'), $xref, 'update')
            );
        }
    }

    /**
     * Default previewer for plugins with no custom preview.
     *
     * @param GedcomRecord $record
     *
     * @return string
     */
    function getActionPreview(GedcomRecord $record)
    {
        $old_lines = preg_split('/[\n]+/', $record->getGedcom());
        $new_lines = preg_split('/[\n]+/', $this->updateRecord($record->getXref(), $record->getGedcom()));
        // Find matching lines using longest-common-subsequence algorithm.
        $lcs = self::LongestCommonSubsequence($old_lines, $new_lines, 0, count($old_lines) - 1, 0, count($new_lines) - 1);

        $diff_lines = array();
        $last_old   = -1;
        $last_new   = -1;
        while ($lcs) {
            list($old, $new) = array_shift($lcs);
            while ($last_old < $old - 1) {
                $diff_lines[] = self::decorateDeletedText($old_lines[++$last_old]);
            }
            while ($last_new < $new - 1) {
                $diff_lines[] = self::decorateInsertedText($new_lines[++$last_new]);
            }
            $diff_lines[] = $new_lines[$new];
            $last_old     = $old;
            $last_new     = $new;
        }
        while ($last_old < count($old_lines) - 1) {
            $diff_lines[] = self::decorateDeletedText($old_lines[++$last_old]);
        }
        while ($last_new < count($new_lines) - 1) {
            $diff_lines[] = self::decorateInsertedText($new_lines[++$last_new]);
        }

        return '<pre>' . self::createEditLinks(implode("\n", $diff_lines)) . '</pre>';
    }

    /**
     * Longest Common Subsequence.
     *
     * @param string[] $X
     * @param string[] $Y
     * @param integer  $x1
     * @param integer  $x2
     * @param integer  $y1
     * @param integer  $y2
     *
     * @return array
     */
    private static function LongestCommonSubsequence($X, $Y, $x1, $x2, $y1, $y2)
    {
        if ($x2 - $x1 >= 0 && $y2 - $y1 >= 0) {
            if ($X[$x1] == $Y[$y1]) {
                // Match at start of sequence
                $tmp = self::LongestCommonSubsequence($X, $Y, $x1 + 1, $x2, $y1 + 1, $y2);
                array_unshift($tmp, array(
                    $x1,
                    $y1
                ));

                return $tmp;
            } elseif ($X[$x2] == $Y[$y2]) {
                // Match at end of sequence
                $tmp = self::LongestCommonSubsequence($X, $Y, $x1, $x2 - 1, $y1, $y2 - 1);
                array_push($tmp, array(
                    $x2,
                    $y2
                ));

                return $tmp;
            } else {
                // No match.  Look for subsequences
                $tmp1 = self::LongestCommonSubsequence($X, $Y, $x1, $x2, $y1, $y2 - 1);
                $tmp2 = self::LongestCommonSubsequence($X, $Y, $x1, $x2 - 1, $y1, $y2);

                return count($tmp1) > count($tmp2) ? $tmp1 : $tmp2;
            }
        } else {
            // One array is empty - end recursion
            return array();
        }
    }

    /**
     * Decorate inserted text
     *
     * @param string $text
     *
     * @return string
     */
    static function decorateInsertedText($text)
    {
        return '<span class="added_text">' . $text . '</span>';
    }

    /**
     * Decorate deleted text
     *
     * @param string $text
     *
     * @return string
     */
    static function decorateDeletedText($text)
    {
        return '<span class="deleted_text">' . $text . '</span>';
    }

    /**
     * Converted gedcom links into editable links
     *
     * @param string $gedrec
     *
     * @return string
     */
    static function createEditLinks($gedrec)
    {
        return preg_replace(
            "/@([^#@\n]+)@/m",
            '<a href="#" onclick="return edit_raw(\'\\1\');">@\\1@</a>',
            $gedrec
        );
    }
}
