<?php
// Header for colors theme
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}
echo
	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
	'<html xmlns="http://www.w3.org/1999/xhtml" ', WT_I18N::html_markup(), '>',
	'<head>',
	'<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />',
	'<title>', htmlspecialchars($title), '</title>',
	header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL),
	'<link rel="icon" href="', WT_THEME_URL, 'favicon.png" type="image/png" />',
	'<link rel="stylesheet" href="', WT_STATIC_URL, 'js/jquery/css/jquery-ui.custom.css" type="text/css"  />',
	'<link rel="stylesheet" href="', WT_THEME_URL, 'css/colors.css" type="text/css" />',
	'<link rel="stylesheet" href="', $stylesheet, '" type="text/css" media="all" />';

switch ($BROWSERTYPE) {
//case 'chrome': uncomment when chrome.css file needs to be added, or add others as needed
case 'msie':
	echo '<link type="text/css" rel="stylesheet" href="', WT_THEME_URL, $BROWSERTYPE, '.css" />';
	break;
}

// Additional css files required (Only if Lightbox installed)
if (WT_USE_LIGHTBOX) {
		echo '<link rel="stylesheet" type="text/css" href="', WT_STATIC_URL, WT_MODULES_DIR, 'lightbox/css/album_page.css" media="screen" />';
}

echo
	$javascript,
	'</head>',
	'<body id="body">';
?>
<!-- Remove submenu from home -->
<script type="text/javascript">
jQuery(document).ready(function() {
	var obj = {};
	var num = 0;
    var num = jQuery('#menu-tree ul li').length; 
	if(num == 2) { 
		jQuery('#menu-tree ul').remove();
	}
});
</script>
<!-- begin header section -->
<?php

if  ($view!='simple') { // Use "simple" headers for popup windows
	echo
	// Top row left
	'<div id="header">',
	'<span class="title">';
		print_gedcom_title_link();
	echo 
	'</span>';

	// Top row right 
	echo 
	'<div class="optionsMenu" >',
	'<ul class="makeMenu">';

	if (WT_USER_ID) {
		echo '<li><a href="edituser.php" class="link">', getUserFullName(WT_USER_ID), '</a></li><li>', logout_link(), '</li>';
		if (WT_USER_CAN_ACCEPT && exists_pending_change()) {
			echo ' <li><a href="javascript:;" onclick="window.open(\'edit_changes.php\',\'_blank\',\'width=600,height=500,resizable=1,scrollbars=1\'); return false;" style="color:red;">', WT_I18N::translate('Pending changes'), '</a></li>';
		}
	} else {
		echo '<li>', login_link(),'</li>';
	}
	$menu=WT_MenuBar::getFavoritesMenu();
	if ($menu) {
		echo $menu->getMenuAsList();
	}
	$menu=WT_MenuBar::getLanguageMenu();
	if ($menu) {
		echo $menu->getMenuAsList();
	}
	$menu=WT_MenuBar::getThemeMenu();
	if ($menu) {
		echo $menu->getMenuAsList();
		$allow_color_dropdown=true;
	} else {
		$allow_color_dropdown=false;
	}
	if ($allow_color_dropdown) {
		echo color_theme_dropdown();
	}
	echo
		'<li>',
			'<form style="display:inline;" action="search.php" method="get">',
			'<input type="hidden" name="action" value="general" />',
			'<input type="hidden" name="topsearch" value="yes" />',
			'<input type="text" name="query" size="10" value="', WT_I18N::translate('Search'), '" onfocus="if (this.value==\'', WT_I18N::translate('Search'), '\') this.value=\'\'; focusHandler();" onblur="if (this.value==\'\') this.value=\'', WT_I18N::translate('Search'), '\';" />',
			'<input type="image" src="', WT_THEME_URL, 'images/go.png', '" align="top" alt="', WT_I18N::translate('Search'), '" title="', WT_I18N::translate('Search'), '" />',
			'</form>',
		'</li>',
	'</ul>',
	'</div>',
	'</div>'; // end header

	// Second Row menu and palette selection
	// Menu
	$menu_items=array(
		WT_MenuBar::getGedcomMenu(), 
		WT_MenuBar::getMyPageMenu(),
		WT_MenuBar::getChartsMenu(),
		WT_MenuBar::getListsMenu(),
		WT_MenuBar::getCalendarMenu(),
		WT_MenuBar::getReportsMenu(),
		WT_MenuBar::getSearchMenu(),
	);
	foreach (WT_MenuBar::getModuleMenus() as $menu) {
		$menu_items[]=$menu;
	}
	$menu_items[]=WT_MenuBar::getHelpMenu();

	// Print the menu bar
	echo
	'<div id="topMenu">',
		'<ul id="main-menu">'; 
		foreach ($menu_items as $menu) {
			if ($menu) {
			echo getMenuAsCustomList($menu);
			}
		}
	unset($menu_items, $menu);
	echo
	'</ul>';


echo 
	'</div>'; // close topMenu
}
// end header section -->
?>
<!-- end menu section -->
<!-- begin content section -->
<div id="content">
