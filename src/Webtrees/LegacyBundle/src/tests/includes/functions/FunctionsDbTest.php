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

use PHPUnit_Framework_TestCase;

/**
 * Unit tests for the global functions in the file includes/functions/functions_db.php
 */
class FunctionsDbTest extends PHPUnit_Framework_TestCase
{
    /**
     * Prepare the environment for these tests
     *
     * @return void
     */
    public function setUp()
    {
    }

    /**
     * Test that function get_user_blocks() exists in the correct namespace.
     *
     * @return void
     */
    public function testFunctionGetUserBlocksExists()
    {
        $this->assertEquals(function_exists(__NAMESPACE__ . '\\get_user_blocks'), true);
    }

}
