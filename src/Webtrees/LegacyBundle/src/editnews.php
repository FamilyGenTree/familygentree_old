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
use PDO;

define('WT_SCRIPT_NAME', 'editnews.php');
Application::i()->init()->started();

$controller = Application::i()->setActiveController(new SimpleController());
$controller
    ->setPageTitle(I18N::translate('Add/edit a journal/news entry'))
    ->restrictAccess(Auth::isMember())
    ->pageHeader();

$action    = Filter::get('action', 'compose|save', 'compose');
$news_id   = Filter::getInteger('news_id');
$user_id   = Filter::get('user_id', WT_REGEX_INTEGER, Filter::post('user_id', WT_REGEX_INTEGER));
$gedcom_id = Filter::get('gedcom_id', WT_REGEX_INTEGER, Filter::post('gedcom_id', WT_REGEX_INTEGER));
$date      = Filter::postInteger('date', 0, PHP_INT_MAX, WT_TIMESTAMP);
$title     = Filter::post('title');
$text      = Filter::post('text');

switch ($action) {
    case 'compose':
        if (array_key_exists('ckeditor', Module::getActiveModules())) {
            ckeditor_WT_Module::enableEditor($controller);
        }

        echo '<h3>' . I18N::translate('Add/edit a journal/news entry') . '</h3>';
        echo '<form style="overflow: hidden;" name="messageform" method="post" action="editnews.php?action=save&news_id=' . $news_id . '">';
        if ($news_id) {
            $news = Database::i()->prepare("SELECT SQL_CACHE news_id AS id, user_id, gedcom_id, UNIX_TIMESTAMP(updated) AS date, subject, body FROM `##news` WHERE news_id=?")
                            ->execute(array($news_id))
                            ->fetchOneRow(PDO::FETCH_ASSOC);
        } else {
            $news              = array();
            $news['user_id']   = $user_id;
            $news['gedcom_id'] = $gedcom_id;
            $news['date']      = WT_TIMESTAMP;
            $news['subject']   = '';
            $news['body']      = '';
        }
        echo '<input type="hidden" name="user_id" value="' . $news['user_id'] . '">';
        echo '<input type="hidden" name="gedcom_id" value="' . $news['gedcom_id'] . '">';
        echo '<input type="hidden" name="date" value="' . $news['date'] . '">';
        echo '<table>';
        echo '<tr><th style="text-align:left;font-weight:900;" dir="auto;">' . I18N::translate('Title:') . '</th><tr>';
        echo '<tr><td><input type="text" name="title" size="50" dir="auto" autofocus value="' . $news['subject'] . '"></td></tr>';
        echo '<tr><th valign="top" style="text-align:left;font-weight:900;" dir="auto;">' . I18N::translate('Entry text:') . '</th></tr>';
        echo '<tr><td>';
        echo '<textarea name="text" class="html-edit" cols="80" rows="10" dir="auto">' . Filter::escapeHtml($news['body']) . '</textarea>';
        echo '</td></tr>';
        echo '<tr><td><input type="submit" value="' . I18N::translate('save') . '"></td></tr>';
        echo '</table>';
        echo '</form>';
        break;
    case 'save':
        if ($news_id) {
            Database::i()->prepare("UPDATE `##news` SET subject=?, body=?, updated=FROM_UNIXTIME(?) WHERE news_id=?")
                    ->execute(array(
                                  $title,
                                  $text,
                                  $date,
                                  $news_id
                              ));
        } else {
            Database::i()->prepare("INSERT INTO `##news` (user_id, gedcom_id, subject, body) VALUES (NULLIF(?, ''), NULLIF(?, '') ,? ,?)")
                    ->execute(array(
                                  $user_id,
                                  $gedcom_id,
                                  $title,
                                  $text
                              ));
        }

        $controller->addInlineJavascript('window.opener.location.reload();window.close();');
        break;
}
