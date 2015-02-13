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

    /** @var string[] All currently installed themes */
    private static $installed_themes;

    /**
     * Create a list of all themes available on the system, including
     * any custom themes.
     *
     * @return string[]
     */
    public static function installedThemes()
    {
        if (self::$installed_themes === null) {
            self::$installed_themes = array();
            foreach (glob(WT_ROOT . 'themes/*/theme.php') as $theme_path) {
                $theme = require $theme_path;
                // Themes beginning with an underscore are reserved for special use.
                if (substr_compare($theme->themeId(), '_', 0, 1) !== 0) {
                    self::$installed_themes[] = $theme;
                }
            }
        }

        return self::$installed_themes;
    }

    /**
     * An associative array of theme names, for <select> fields, etc.
     *
     * @return string[]
     */
    public static function themeNames()
    {
        $theme_names = array();
        foreach (self::installedThemes() as $theme) {
            $theme_names[$theme->themeId()] = $theme->themeName();
        }

        return $theme_names;
    }

    /**
     * The currently active theme
     *
     * @param BaseTheme|null $theme
     *
     * @return BaseTheme
     * @throws \LogicException
     */
    public static function theme(BaseTheme $theme = null)
    {

        if ($theme) {
            self::$theme = $theme;
        } elseif (!self::$theme) {
            throw new \LogicException;
        }

        return self::$theme;
    }
}
