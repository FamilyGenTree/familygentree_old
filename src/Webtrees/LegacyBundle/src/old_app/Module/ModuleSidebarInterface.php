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
 * Interface ModuleSidebarInterface - Classes and libraries for module system
 */
interface ModuleSidebarInterface
{
    /**
     * The user can change the order of sidebars.  Until they do this, they are shown in this order.
     *
     * @return integer
     */
    public function defaultSidebarOrder();

    /**
     * Load this sidebar synchronously.
     *
     * @return string
     */
    public function getSidebarContent();

    /**
     * Load this sidebar asynchronously.
     *
     * @return string
     */
    public function getSidebarAjaxContent();

    /**
     * Does this sidebar have anything to display for this individual?
     *
     * @return boolean
     */
    public function hasSidebarContent();
}
