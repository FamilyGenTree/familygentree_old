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
use Fisharebest\ExtCalendar\JewishCalendar;
use Rhumsaa\Uuid\Uuid;

/**
 * Class yahrzeit_WT_Module
 */
class yahrzeit_WT_Module extends Module implements ModuleBlockInterface
{
    /** {@inheritdoc} */
    public function getTitle()
    {
        return /* I18N: Name of a module.  Yahrzeiten (the plural of Yahrzeit) are special anniversaries of deaths in the Hebrew faith/calendar. */
            I18N::translate('Yahrzeiten');
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return /* I18N: Description of the “Yahrzeiten” module.  A “Hebrew death” is a death where the date is recorded in the Hebrew calendar. */
            I18N::translate('A list of the Hebrew death anniversaries that will occur in the near future.');
    }

    /** {@inheritdoc} */
    public function getBlock($block_id, $template = true, $cfg = null)
    {
        global $ctype;

        $controller = Application::i()->getActiveController();

        $days      = FunctionsDbPhp::i()->get_block_setting($block_id, 'days', '7');
        $infoStyle = FunctionsDbPhp::i()->get_block_setting($block_id, 'infoStyle', 'table');
        $calendar  = FunctionsDbPhp::i()->get_block_setting($block_id, 'calendar', 'jewish');
        $block     = FunctionsDbPhp::i()->get_block_setting($block_id, 'block', '1');

        if ($cfg) {
            foreach (array(
                         'days',
                         'infoStyle',
                         'block'
                     ) as $name) {
                if (array_key_exists($name, $cfg)) {
                    $$name = $cfg[$name];
                }
            }
        }

        $startjd = WT_CLIENT_JD;
        $endjd   = WT_CLIENT_JD + $days - 1;

        $id    = $this->getName() . $block_id;
        $class = $this->getName() . '_block';
        if ($ctype === 'gedcom' && WT_USER_GEDCOM_ADMIN || $ctype === 'user' && Auth::check()) {
            $title = '<i class="icon-admin" title="' . I18N::translate('Configure') . '" onclick="modalDialog(\'block_edit.php?block_id=' . $block_id . '\', \'' . $this->getTitle() . '\');"></i>';
        } else {
            $title = '';
        }
        $title .= $this->getTitle();

        $content = '';
        // The standard anniversary rules cover most of the Yahrzeit rules, we just
        // need to handle a few special cases.
        // Fetch normal anniversaries...
        $yahrzeits = array();
        for ($jd = $startjd - 1; $jd <= $endjd + $days; ++$jd) {
            foreach (FunctionsDbPhp::i()->get_anniversary_events($jd, 'DEAT _YART') as $fact) {
                // Exact hebrew dates only
                $date = $fact->getDate();
                if ($date->MinDate() instanceof JewishDate && $date->MinJD() == $date->MaxJD()) {
                    $fact->jd    = $jd;
                    $yahrzeits[] = $fact;
                }
            }
        }

        // ...then adjust dates
        $jewish_calendar = new JewishCalendar;

        foreach ($yahrzeits as $yahrzeit) {
            if ($yahrzeit->getTag() === 'DEAT') {
                $today = new JewishDate($yahrzeit->jd);
                $hd    = $yahrzeit->getDate()
                                  ->MinDate();
                $hd1   = new JewishDate($hd);
                $hd1->y += 1;
                $hd1->setJdFromYmd();
                // Special rules.  See http://www.hebcal.com/help/anniv.html
                // Everything else is taken care of by our standard anniversary rules.
                if ($hd->d == 30 && $hd->m == 2 && $hd->y != 0 && $hd1->daysInMonth() < 30) {
                    // 30 CSH - Last day in CSH
                    $yahrzeit->jd = $jewish_calendar->ymdToJd($today->y, 3, 1) - 1;
                } elseif ($hd->d == 30 && $hd->m == 3 && $hd->y != 0 && $hd1->daysInMonth() < 30) {
                    // 30 KSL - Last day in KSL
                    $yahrzeit->jd = $jewish_calendar->ymdToJd($today->y, 4, 1) - 1;
                } elseif ($hd->d == 30 && $hd->m == 6 && $hd->y != 0 && $today->daysInMonth() < 30 && !$today->isLeapYear()) {
                    // 30 ADR - Last day in SHV
                    $yahrzeit->jd = $jewish_calendar->ymdToJd($today->y, 6, 1) - 1;
                }
            }
        }

        switch ($infoStyle) {
            case 'list':
                foreach ($yahrzeits as $yahrzeit) {
                    if ($yahrzeit->jd >= $startjd && $yahrzeit->jd < $startjd + $days) {
                        $ind = $yahrzeit->getParent();
                        $content .= "<a href=\"" . $ind->getHtmlUrl() . "\" class=\"list_item name2\">" . $ind->getFullName() . "</a>" . $ind->getSexImage();
                        $content .= "<div class=\"indent\">";
                        $content .= $yahrzeit->getDate()
                                             ->Display(true);
                        $content .= ', ' . I18N::translate('%s year anniversary', $yahrzeit->anniv);
                        $content .= "</div>";
                    }
                }
                break;
            case 'table':
            default:
                $table_id = Uuid::uuid4(); // table requires a unique ID
                $controller
                    ->addExternalJavascript(WT_JQUERY_DATATABLES_JS_URL)
                    ->addInlineJavascript('
					jQuery("#' . $table_id . '").dataTable({
						dom: \'t\',
						' . I18N::datatablesI18N() . ',
						autoWidth: false,
						paginate: false,
						lengthChange: false,
						filter: false,
						info: true,
						jQueryUI: true,
						sorting: [[5,"asc"]],
						columns: [
							/* 0-name */ { dataSort: 1 },
							/* 1-NAME */ { visible: false },
							/* 2-date */ { dataSort: 3 },
							/* 3-DATE */ { visible: false },
							/* 4-Aniv */ { class: "center"},
							/* 5-yart */ { dataSort: 6 },
							/* 6-YART */ { visible: false }
						]
					});
					jQuery("#' . $table_id . '").css("visibility", "visible");
					jQuery(".loading-image").css("display", "none");
				');
                $content = '';
                $content .= '<div class="loading-image">&nbsp;</div>';
                $content .= '<table id="' . $table_id . '" class="width100" style="visibility:hidden;">';
                $content .= '<thead><tr>';
                $content .= '<th>' . WT_Gedcom_Tag::getLabel('NAME') . '</th>';
                $content .= '<th>' . WT_Gedcom_Tag::getLabel('NAME') . '</th>';
                $content .= '<th>' . WT_Gedcom_Tag::getLabel('DEAT') . '</th>';
                $content .= '<th>DEAT</th>';
                $content .= '<th><i class="icon-reminder" title="' . I18N::translate('Anniversary') . '"></i></th>';
                $content .= '<th>' . WT_Gedcom_Tag::getLabel('_YART') . '</th>';
                $content .= '<th>_YART</th>';
                $content .= '</tr></thead><tbody>';

                foreach ($yahrzeits as $yahrzeit) {
                    if ($yahrzeit->jd >= $startjd && $yahrzeit->jd < $startjd + $days) {
                        $content .= '<tr>';
                        $ind = $yahrzeit->getParent();
                        // Individual name(s)
                        $name = $ind->getFullName();
                        $url  = $ind->getHtmlUrl();
                        $content .= '<td>';
                        $content .= '<a href="' . $url . '">' . $name . '</a>';
                        $content .= $ind->getSexImage();
                        $addname = $ind->getAddName();
                        if ($addname) {
                            $content .= '<br><a href="' . $url . '">' . $addname . '</a>';
                        }
                        $content .= '</td>';
                        $content .= '<td>' . $ind->getSortName() . '</td>';

                        // death/yahrzeit event date
                        $content .= '<td>' . $yahrzeit->getDate()
                                                      ->Display() . '</td>';
                        $content .= '<td>' . $yahrzeit->getDate()
                                                      ->minJD() . '</td>'; // sortable date

                        // Anniversary
                        $content .= '<td>' . $yahrzeit->anniv . '</td>';

                        // upcomming yahrzeit dates
                        switch ($calendar) {
                            case 'gregorian':
                                $today = new GregorianDate($yahrzeit->jd);
                                break;
                            case 'jewish':
                            default:
                                $today = new JewishDate($yahrzeit->jd);
                                break;
                        }
                        $td = new Date($today->format('%@ %A %O %E'));
                        $content .= '<td>' . $td->display() . '</td>';
                        $content .= '<td>' . $td->minJD() . '</td>'; // sortable date

                        $content .= '</tr>';
                    }
                }
                $content .= '</tbody></table>';

                break;
        }

        if ($template) {
            if ($block) {
                $class .= ' small_inner_block';
            }

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
        if (Filter::postBool('save') && Filter::checkCsrf()) {
            FunctionsDbPhp::i()->set_block_setting($block_id, 'days', Filter::postInteger('days', 1, 30, 7));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'infoStyle', Filter::post('infoStyle', 'list|table', 'table'));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'calendar', Filter::post('calendar', 'jewish|gregorian', 'jewish'));
            FunctionsDbPhp::i()->set_block_setting($block_id, 'block', Filter::postBool('block'));
        }

        $days      = FunctionsDbPhp::i()->get_block_setting($block_id, 'days', '7');
        $infoStyle = FunctionsDbPhp::i()->get_block_setting($block_id, 'infoStyle', 'table');
        $calendar  = FunctionsDbPhp::i()->get_block_setting($block_id, 'calendar', 'jewish');
        $block     = FunctionsDbPhp::i()->get_block_setting($block_id, 'block', '1');

        echo '<tr><td class="descriptionbox wrap width33">';
        echo I18N::translate('Number of days to show');
        echo '</td><td class="optionbox">';
        echo '<input type="text" name="days" size="2" value="' . $days . '">';
        echo ' <em>', I18N::plural('maximum %d day', 'maximum %d days', 30, 30), '</em>';
        echo '</td></tr>';

        echo '<tr><td class="descriptionbox wrap width33">';
        echo I18N::translate('Presentation style');
        echo '</td><td class="optionbox">';
        echo FunctionsEdit::i()->select_edit_control('infoStyle', array(
            'list'  => I18N::translate('list'),
            'table' => I18N::translate('table')
        ), null, $infoStyle, '');
        echo '</td></tr>';

        echo '<tr><td class="descriptionbox wrap width33">';
        echo I18N::translate('Calendar');
        echo '</td><td class="optionbox">';
        echo FunctionsEdit::i()->select_edit_control('calendar', array(
            'jewish'    => JewishDate::calendarName(),
            'gregorian' => GregorianDate::calendarName(),
        ), null, $calendar, '');
        echo '</td></tr>';

        echo '<tr><td class="descriptionbox wrap width33">';
        echo /* I18N: label for a yes/no option */
        I18N::translate('Add a scrollbar when block contents grow');
        echo '</td><td class="optionbox">';
        echo FunctionsEdit::i()->edit_field_yes_no('block', $block);
        echo '</td></tr>';
    }
}
