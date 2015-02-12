<?php
namespace Fisharebest\Webtrees;

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


Application::i()->init()
           ->started();

// These theme globals are horribly abused.
global $bwidth,$bheight,$basexoffset,$baseyoffset,$bxspacing,$byspacing,$Dbwidth,$Dbheight;

$bwidth      = Theme::theme()
                    ->parameter('chart-box-x');
$bheight     = Theme::theme()
                    ->parameter('chart-box-y');
$basexoffset = Theme::theme()
                    ->parameter('chart-offset-x');
$baseyoffset = Theme::theme()
                    ->parameter('chart-offset-y');
$bxspacing   = Theme::theme()
                    ->parameter('chart-spacing-x');
$byspacing   = Theme::theme()
                    ->parameter('chart-spacing-y');
$Dbwidth     = Theme::theme()
                    ->parameter('chart-descendancy-box-x');
$Dbheight    = Theme::theme()
                    ->parameter('chart-descendancy-box-y');
