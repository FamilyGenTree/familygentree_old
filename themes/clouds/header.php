<?php
/**
 * Header for Clouds theme
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2011 webtrees development team.
 *
 * Derived from PhpGedView Cloudy theme
 * Original author w.a. bastein http://genealogy.bastein.biz
 * Copyright (C) 2010 PGV Development Team.  All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package webtrees
 * @subpackage Themes
 * @version $Id$
 */

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

global $modules;

// Definitions to simplify logic on pages with right-to-left languages
// TODO: merge this into the trunk?
if ($TEXT_DIRECTION=='ltr') {
	define ('WT_CSS_ALIGN',         'left');
	define ('WT_CSS_REVERSE_ALIGN', 'right');
} else {
	define ('WT_CSS_ALIGN',         'right');
	define ('WT_CSS_REVERSE_ALIGN', 'left');
}

echo
	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
	'<html xmlns="http://www.w3.org/1999/xhtml" ', WT_I18N::html_markup(), '>',
	'<head>',
	'<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />',
	'<title>', htmlspecialchars($title), '</title>',
	'<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />';

if (!empty($LINK_CANONICAL)) {
	echo '<link rel="canonical" href="', $LINK_CANONICAL, '" />';
}
if (!empty($META_DESCRIPTION)) {
	echo '<meta name="description" content="', htmlspecialchars($META_DESCRIPTION), '" />';
}
echo '<meta name="robots" content="', $META_ROBOTS, '" />';
if (!empty($META_GENERATOR)) {
	echo '<meta name="generator" content="', $META_GENERATOR, '" />';
}

echo
	'<link type="text/css" href="js/jquery/css/jquery-ui.custom.css" rel="Stylesheet" />',
	'<link rel="stylesheet" href="', $stylesheet, '" type="text/css" media="all" />',
	'<link rel="stylesheet" href="', $print_stylesheet, '" type="text/css" media="print" />';
	
if ($TEXT_DIRECTION=='rtl') { 
	echo '<link rel="stylesheet" href="', $rtl_stylesheet, '" type="text/css" media="all" />';
}

if (file_exists(WT_THEME_DIR.$BROWSERTYPE.'.css')) {
	echo '<link rel="stylesheet" href="', WT_THEME_DIR.$BROWSERTYPE, '.css" type="text/css" media="all" />';
}

if (WT_USE_LIGHTBOX) {
	if ($TEXT_DIRECTION=='rtl') {
		echo
			'<link rel="stylesheet" href="', WT_MODULES_DIR, 'lightbox/css/clearbox_music_RTL.css" type="text/css" />',
			'<link rel="stylesheet" href="', WT_MODULES_DIR, 'lightbox/css/album_page_RTL_ff.css" type="text/css" media="screen" />';
	} else {
		echo
			'<link rel="stylesheet" href="', WT_MODULES_DIR, 'lightbox/css/clearbox_music.css" type="text/css" />',
			'<link rel="stylesheet" href="', WT_MODULES_DIR, 'lightbox/css/album_page.css" type="text/css" media="screen" />';
	}
}

echo
	'<link rel="stylesheet" href="', $modules, '" type="text/css" />',
	$javascript,
	'</head>',
	'<body id="body">';

// begin header section -->
if ($view!='simple') {
	echo 
	'<div id="clouds-container">',
		'<div id="header">',
			'<div class="header" >',
				'<span class="title">';
				print_gedcom_title_link(TRUE);
				echo 
				'</span>',
				

				'<div class="hsearch">';
				if (empty($SEARCH_SPIDER)) {
					echo 
					'<form action="search.php" method="post">',
					'<input type="hidden" name="action" value="general" />',
					'<input type="hidden" name="topsearch" value="yes" />',
					'<input type="text" name="query" size="12" value="', WT_I18N::translate('Search'), '"',
							'onfocus="if (this.value==\'', WT_I18N::translate('Search'), '\') this.value=\'\'; focusHandler();"',
							'onblur="if (this.value==\'\') this.value=\'', WT_I18N::translate('Search'), '\';" />',
					'<input align="top" type="image" title="Search" alt="Search" src="themes/clouds/images/go.gif">',
					'</form>',
				'</div>';
				}
			echo 
			'</div>',
	'</div>';
?>
<!-- end header section -->
<!-- begin menu section -->
<?php
	echo
		'<div id="topMenu">',
		'<ul id="main-menu">'; 
		$menu=WT_MenuBar::getGedcomMenu();
		if ($menu) {
			$menu->addIcon(null);
			echo $menu->getMenuAsList();
		}
		$menu=WT_MenuBar::getMyPageMenu();
		if ($menu) {
			echo $menu->getMenuAsList();
		}
		$menu=WT_MenuBar::getChartsMenu();
		if ($menu) {
			$menu->addIcon(null);
			echo $menu->getMenuAsList();
		}
		$menu=WT_MenuBar::getListsMenu();
		if ($menu) {
			$menu->addIcon(null);
			echo $menu->getMenuAsList();
		}
		$menu=WT_MenuBar::getCalendarMenu();
		if ($menu) {
			$menu->addIcon(null);
			echo $menu->getMenuAsList();
		}
		$menu=WT_MenuBar::getReportsMenu();
		if ($menu) {
			$menu->addIcon(null);
			echo $menu->getMenuAsList();
		}
		$menu=WT_MenuBar::getSearchMenu();
		if ($menu) {
			$menu->addIcon(null);
			echo $menu->getMenuAsList();
		}
		$menus=WT_MenuBar::getModuleMenus();
		foreach ($menus as $menu) {
			if ($menu) {
				$menu->addIcon(null);
			echo $menu->getMenuAsList();
			}
		}
		$menu=WT_MenuBar::getHelpMenu();
		if ($menu) {
			$menu->addIcon(null);
			echo $menu->getMenuAsList();
		}
	echo 
	'</ul>';
	if (empty($SEARCH_SPIDER)) {
		echo 
		'<span id="menu-right">',
		'<ul class="makeMenu">';
		if (WT_USER_ID) {
			echo '<li><a href="edituser.php" class="link">', getUserFullName(WT_USER_ID), '</a></li><li>', logout_link(), '</li>';
			if (WT_USER_CAN_ACCEPT && exists_pending_change()) {
				echo ' <li><a href="javascript:;" onclick="window.open(\'edit_changes.php\',\'_blank\',\'width=600,height=500,resizable=1,scrollbars=1\'); return false;" style="color:red;">', WT_I18N::translate('Pending changes'), '</a></li>';
			}
		} else {
			echo '<li>', login_link(),'</li>';
		}
		echo WT_MenuBar::getFavoritesMenu()->getMenuAsList();
		$language_menu=WT_MenuBar::getLanguageMenu();
		if ($language_menu) {
			echo $language_menu->getMenuAsList();
		}
		if (get_gedcom_setting(WT_GED_ID, 'ALLOW_THEME_DROPDOWN') && get_site_setting('ALLOW_USER_THEMES')) {
			echo WT_MenuBar::getThemeMenu()->getMenuAsList();
			$allow_color_dropdown=true;
		} else {
			$allow_color_dropdown=false;
		}
	}
				echo '</span></div>';
	

}
?>
<!-- end menu section -->
<!-- begin content section -->
<div id="content">
