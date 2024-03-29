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

/**
 * Class FlashMessages - Flash messages allow us to generate messages
 * in one context, and display them in another.
 */
class FlashMessages
{
    /**
     * Add a new message to the session storage.
     *
     * @param string $text
     * @param string $status "success", "info", "warning" or "danger"
     */
    public static function addMessage($text, $status = 'info')
    {
        $flash_messenger = Application::i()->getSession()->getFlashMessageBag();

        $flash_messenger->add($status,$text);
    }

    /**
     * Get the current messages, and remove them from session storage.
     *
     * @return string[]
     */
    public static function getMessages()
    {
        $flash_messenger = Application::i()->getSession()->getFlashMessageBag();

        $messages = array();

        // Get messages from previous requests
        foreach ($flash_messenger->all() as $message) {
            $messages[] = $message;
        }

        return $messages;
    }
}
