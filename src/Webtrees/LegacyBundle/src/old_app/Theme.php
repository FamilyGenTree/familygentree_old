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
 * Class Theme - provide access to the current theme.
 */
class Theme
{
    /** @var BaseTheme The current theme */
    private static $theme;

    /**
     * The currently active theme
     *
     * @param BaseTheme|null $theme
     *
     * @return BaseTheme
     * @throws \LogicException
     */
    public static function theme()
    {

        if (!self::$theme) {
            throw new \LogicException("Theme not set.");
        }

        return self::$theme;
    }

    /**
     * @param \Webtrees\LegacyBundle\Legacy\BaseTheme $theme
     *
     * @return \Webtrees\LegacyBundle\Legacy\BaseTheme
     */
    public static function setTheme(BaseTheme $theme) {
        self::$theme = $theme;
        return self::$theme;
    }
}
