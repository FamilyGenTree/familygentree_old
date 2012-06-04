<?php
// Googlemap Module help text.
//
// This file is included from the application help_text.php script.
// It simply needs to set $title and $text for the help topic $help_topic
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

if (!defined('WT_WEBTREES') || !defined('WT_SCRIPT_NAME') || WT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'GOOGLEMAP_MAP_ZOOM':
	$title=WT_I18N::translate('Zoom factor of map');
	$text=WT_I18N::translate('Minimum and maximum zoom factor for the Google map. 1 is the full map, 15 is single house. Note that 15 is only available in certain areas.');
	break;

case 'GOOGLEMAP_PRECISION':
	$title=WT_I18N::translate('Precision of the latitude and longitude');
	$text=WT_I18N::translate('This specifies the precision of the different levels when entering new geographic locations. For example a country will be specified with precision 0 (=0 digits after the decimal point), while a town needs 3 or 4 digits.');
	break;

case 'GM_DEFAULT_LEVEL_0':
	$title=WT_I18N::translate('Default value for top-level');
	$text=WT_I18N::translate('Here the default level for the highest level in the place-hierarchy can be defined. If a place cannot be found this name is added as the highest level (country) and the database is searched again.');
	break;

case 'GM_NAME_PREFIX_SUFFIX':
	$title=WT_I18N::translate('Optional prefixes and suffixes');
	$text=WT_I18N::translate('Some place names may be written with optional prefixes and suffixes.  For example “Orange” versus “Orange County”.  If the family tree contains the full place names, but the geographic database contains the short place names, then you should specify a list of the prefixes and suffixes to be disregarded.  Multiple options should be separated with semicolons.  For example “County;County of” or “Township;Twp;Twp.”.');
	break;

case 'GOOGLEMAP_COORD':
	$title=WT_I18N::translate('Display Map Coordinates');
	$text=WT_I18N::translate('This options sets whether Latitude and Longitude are displayed on the pop-up window attached to map markers.');
	break;

// Help texts for places_edit.php

case 'PLE_PRECISION':
	$title=WT_I18N::translate('Enter precision');
	$text=WT_I18N::translate('Here you can enter the precision. Based on this setting the number of digits that will be used in the latitude and longitude is determined.');
	break;

case 'PLE_ZOOM':
	$title=WT_I18N::translate('Enter zoom level');
	$text=WT_I18N::translate('Here the zoom level can be entered. This value will be used as the minimal value when displaying this geographic location on a map.');
	break;

case 'PLE_ICON':
	$title=WT_I18N::translate('Select an icon');
	$text=WT_I18N::translate('Here an icon can be set or removed. Using this link a flag can be selected. When this geographic location is shown, this flag will be displayed.');
	break;

case 'PLE_FLAGS':
	$title=WT_I18N::translate('Select flag');
	$text=WT_I18N::translate('Using the pull down menu it is possible to select a country, of which a flag can be selected. If no flags are shown, then there are no flags defined for this country.');
	break;

case 'PLIF_LOCALFILE':
	$title=WT_I18N::translate('Enter filename');
	$text=WT_I18N::translate('Select a file from the list of files already on the server which contains the place locations in CSV format.');
	break;

case 'PLE_ACTIVE':
	$title=WT_I18N::translate('Show inactive places');
	$text=
		'<p>'.
		WT_I18N::translate('By default, the list shows only those places which can be found in your family trees.  You may have details for other places, such as those imported in bulk from an external file.  Selecting this option will show all places, including ones that are not currently used.').
		'</p><p class="warning">'.
		WT_I18N::translate('If you have a large number of inactive places, it can be slow to generate the list.').
		'</p>';
	break;

// Help text for Place Hierarchy display

case 'GM_DISP_SHORT_PLACE':
	$title=WT_I18N::translate('Display short placenames');
	$text=WT_I18N::translate('Here you can choose between two types of displaying places names in hierarchy. If set Yes the place has short name or actual level name, if No - full name.<br /><b>Examples:<br />Full name: </b>Chicago, Illinois, USA<br /><b>Short name: </b>Chicago<br /><b>Full name: </b>Illinois, USA<br /><b>Short name: </b>Illinois');
	break;

case 'GM_DISP_COUNT':
	$title=WT_I18N::translate('Display indis and families counts');
	$text=WT_I18N::translate('Here you can specify if the counts of indis and families connected to the place is displayed. Now if GEDCOM file contains many people is recomended to turn it off.');
	break;

case 'GOOGLEMAP_PH_WHEEL':
	$title=WT_I18N::translate('Use mouse wheel for zoom');
	$text=WT_I18N::translate('Here you can specify if the mouse wheel is enebled for zooming.');
	break;

case 'GOOGLEMAP_PH_CONTROLS':
	$title=WT_I18N::translate('Hide map controls');
	$text=WT_I18N::translate('This option allow to hide map controls (i.e. the map type choice) if mouse is outside the map.');
	break;

// Pedigree map

case 'PEDIGREE_MAP_clustersize':
	$title=WT_I18N::translate('Cluster size');
	$text=WT_I18N::translate('The number of markers to be placed at one point before a trail of pins is started in a north east line behind the younger generations.  The \'trail\' is usually only visable at high zoom values.');
	break;

case 'PEDIGREE_MAP_hidelines':
	$title=WT_I18N::translate('Hide lines');
	$text=WT_I18N::translate('Hide the lines connecting the child to each parent if they exist on the map.');
	break;

case 'PEDIGREE_MAP_hideflags':
	$title=WT_I18N::translate('Hide flags');
	$text=WT_I18N::translate('Hide the flags that are configured in the googlemap module.  Usually these are for countries and states. This serves as a visual cue that the markers around the flag are from the general area, and not the specific spot.');
	break;
}
