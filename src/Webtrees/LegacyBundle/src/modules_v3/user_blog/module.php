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
use Fgt\Config;
use Fgt\UrlConstants;
use PDOException;

// Create tables, if not already present
try {
    Database::i()->updateSchema(WT_MODULES_DIR . 'user_blog/db_schema/', 'NB_SCHEMA_VERSION', 3);
} catch (PDOException $ex) {
    // The schema update scripts should never fail.  If they do, there is no clean recovery.
    FlashMessages::addMessage($ex->getMessage(), 'danger');
    header('Location: ' . Config::get(Config::BASE_URL) . 'site-unavailable.php');
    throw $ex;
}

/**
 * Class user_blog_WT_Module
 */
class user_blog_WT_Module extends Module implements ModuleBlockInterface
{
    /** {@inheritdoc} */
    public function getTitle()
    {
        return /* I18N: Name of a module */
            I18N::translate('Journal');
    }

    /** {@inheritdoc} */
    public function getDescription()
    {
        return /* I18N: Description of the “Journal” module */
            I18N::translate('A private area to record notes or keep a journal.');
    }

    /** {@inheritdoc} */
    public function getBlock($block_id, $template = true, $cfg = null)
    {
        global $ctype;

        switch (Filter::get('action')) {
            case 'deletenews':
                $news_id = Filter::getInteger('news_id');
                if ($news_id) {
                    Database::i()->prepare("DELETE FROM `##news` WHERE news_id = ?")
                            ->execute(array($news_id));
                }
                break;
        }
        $block = FunctionsDbPhp::i()->get_block_setting($block_id, 'block', '1');
        if ($cfg) {
            foreach (array('block') as $name) {
                if (array_key_exists($name, $cfg)) {
                    $$name = $cfg[$name];
                }
            }
        }
        $usernews = Database::i()->prepare(
            "SELECT SQL_CACHE news_id, user_id, gedcom_id, UNIX_TIMESTAMP(updated) AS updated, subject, body FROM `##news` WHERE user_id = ? ORDER BY updated DESC"
        )
                            ->execute(array(Auth::id()))
                            ->fetchAll();

        $id    = $this->getName() . $block_id;
        $class = $this->getName() . '_block';
        $title = '';
        $title .= $this->getTitle();
        $content = '';
        if (!$usernews) {
            $content .= I18N::translate('You have not created any journal items.');
        }
        foreach ($usernews as $news) {
            $content .= '<div class="journal_box">';
            $content .= '<div class="news_title">' . $news->subject . '</div>';
            $content .= '<div class="news_date">' . FunctionsDate::i()->format_timestamp($news->updated) . '</div>';
            if ($news->body == strip_tags($news->body)) {
                // No HTML?
                $news->body = nl2br($news->body, false);
            }
            $content .= $news->body . '<br><br>';
            $content .= '<a href="#" onclick="window.open(\'editnews.php?news_id=\'+' . $news->news_id . ', \'_blank\', indx_window_specs); return false;">' . I18N::translate('Edit') . '</a> | ';
            $content .= '<a href="' . UrlConstants::urlEscape(UrlConstants::INDEX_PHP, ['action'  => 'deletenews',
                                                                                        'news_id' => $news->news_id,
                                                                                        'ctype'   => $ctype
                ]) . '" onclick="return confirm(\'' . I18N::translate('Are you sure you want to delete this journal entry?') . "');\">" . I18N::translate('Delete') . '</a><br>';
            $content .= "</div><br>";
        }
        $content .= '<br><a href="#" onclick="window.open(\'editnews.php?user_id=' . Auth::id() . '\', \'_blank\', indx_window_specs); return false;">' . I18N::translate('Add a new journal entry') . '</a>';

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
        return false;
    }

    /** {@inheritdoc} */
    public function isUserBlock()
    {
        return true;
    }

    /** {@inheritdoc} */
    public function isGedcomBlock()
    {
        return false;
    }

    /** {@inheritdoc} */
    public function configureBlock($block_id)
    {
    }
}
