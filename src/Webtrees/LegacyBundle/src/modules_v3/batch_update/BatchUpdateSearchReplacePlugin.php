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
 * Class BatchUpdateSearchReplacePlugin Batch Update plugin: search/replace
 */
class BatchUpdateSearchReplacePlugin extends BatchUpdateBasePlugin
{
    var $search  = null; // Search string
    var $replace = null; // Replace string
    var $method  = null; // simple/wildcards/regex
    var $regex   = null; // Search string, converted to a regex
    var $case    = null; // "i" for case insensitive, "" for case sensitive
    var $error   = null; // Message for bad user parameters

    /**
     * User-friendly name for this plugin.
     *
     * @return string
     */
    public function getName()
    {
        return I18N::translate('Search and replace');
    }

    /**
     * Description / help-text for this plugin.
     *
     * @return string
     */
    public function getDescription()
    {
        return /* I18N: Description of the “Search and replace” option of the batch update module */
            I18N::translate('Search and replace text, using simple searches or advanced pattern matching.');
    }

    /**
     * This plugin will update all types of record.
     *
     * @return string[]
     */
    public function getRecordTypesToUpdate()
    {
        return array(
            'INDI',
            'FAM',
            'SOUR',
            'REPO',
            'NOTE',
            'OBJE'
        );
    }

    /**
     * Does this record need updating?
     *
     * @param string $xref
     * @param string $gedrec
     *
     * @return boolean
     */
    public function doesRecordNeedUpdate($xref, $gedrec)
    {
        return !$this->error && preg_match('/(?:' . $this->regex . ')/mu' . $this->case, $gedrec);
    }

    /**
     * Apply any updates to this record
     *
     * @param string $xref
     * @param string $gedrec
     *
     * @return string
     */
    public function updateRecord($xref, $gedrec)
    {
        // Allow "\n" to indicate a line-feed in replacement text.
        // Back-references such as $1, $2 are handled automatically.
        return preg_replace('/' . $this->regex . '/mu' . $this->case, str_replace('\n', "\n", $this->replace), $gedrec);
    }

    /**
     * Process the user-supplied options.
     */
    public function getOptions()
    {
        parent::getOptions();
        $this->search  = Filter::get('search');
        $this->replace = Filter::get('replace');
        $this->method  = Filter::get('method', 'exact|words|wildcards|regex', 'exact');
        $this->case    = Filter::get('case', 'i');

        $this->error = '';
        switch ($this->method) {
            case 'exact':
                $this->regex = preg_quote($this->search, '/');
                break;
            case 'words':
                $this->regex = '\b' . preg_quote($this->search, '/') . '\b';
                break;
            case 'wildcards':
                $this->regex = '\b' . str_replace(array(
                                                      '\*',
                                                      '\?'
                                                  ), array(
                                                      '.*',
                                                      '.'
                                                  ), preg_quote($this->search, '/')) . '\b';
                break;
            case 'regex':
                $this->regex = $this->search;
                // Check for invalid regular expressions.
                // A valid regex on a null string returns zero.
                // An invalid regex on a null string returns false (and throws a warning).
                if (@preg_match('/' . $this->search . '/', null) === false) {
                    $this->error = '<br><span class="error">' . I18N::translate('The regex appears to contain an error.  It can’t be used.') . '</span>';
                }
                break;
        }
    }

    /**
     * Generate a form to ask the user for options.
     *
     * @return string
     */
    public function getOptionsForm()
    {
        $descriptions = array(
            'exact'     => I18N::translate('Match the exact text, even if it occurs in the middle of a word.'),
            'words'     => I18N::translate('Match the exact text, unless it occurs in the middle of a word.'),
            'wildcards' => I18N::translate('Use a “?” to match a single character, use “*” to match zero or more characters.'),
            'regex'     => /* I18N: http://en.wikipedia.org/wiki/Regular_expression */
                I18N::translate('Regular expressions are an advanced pattern matching technique.') . '<br>' . /* I18N: %s is a URL */
                I18N::translate('See %s for more information.', '<a href="http://php.net/manual/regexp.reference.php" target="_blank">php.net/manual/regexp.reference.php</a>'),
        );

        return
            '<tr><th>' . I18N::translate('Search text/pattern') . '</th>' .
            '<td>' .
            '<input name="search" size="40" value="' . Filter::escapeHtml($this->search) .
            '" onchange="this.form.submit();"></td></tr>' .
            '<tr><th>' . I18N::translate('Replacement text') . '</th>' .
            '<td>' .
            '<input name="replace" size="40" value="' . Filter::escapeHtml($this->replace) .
            '" onchange="this.form.submit();"></td></tr>' .
            '<tr><th>' . I18N::translate('Search method') . '</th>' .
            '<td><select name="method" onchange="this.form.submit();">' .
            '<option value="exact" ' . ($this->method == 'exact' ? 'selected'
                : '') . '>' . I18N::translate('Exact text') . '</option>' .
            '<option value="words" ' . ($this->method == 'words' ? 'selected'
                : '') . '>' . I18N::translate('Whole words only') . '</option>' .
            '<option value="wildcards" ' . ($this->method == 'wildcards' ? 'selected'
                : '') . '>' . I18N::translate('Wildcards') . '</option>' .
            '<option value="regex" ' . ($this->method == 'regex' ? 'selected'
                : '') . '>' . I18N::translate('Regular expression') . '</option>' .
            '</select><br><em>' . $descriptions[$this->method] . '</em>' . $this->error . '</td></tr>' .
            '<tr><th>' . I18N::translate('Case insensitive') . '</th>' .
            '<td>' .
            '<input type="checkbox" name="case" value="i" ' . ($this->case == 'i' ? 'checked'
                : '') . '" onchange="this.form.submit();">' .
            '<br><em>' . I18N::translate('Tick this box to match both upper and lower case letters.') . '</em></td></tr>' .
            parent::getOptionsForm();
    }
}
