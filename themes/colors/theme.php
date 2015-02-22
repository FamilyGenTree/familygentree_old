<?php
// Colors theme
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2010  PGV Development Team.  All rights reserved.
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

//-- print color theme sub type change dropdown box
function color_theme_dropdown() {
	global $COLOR_THEME_LIST;
	
	$menu=new WT_Menu(WT_I18N::translate('Color Palette'));
	$menu->addClass('thememenuitem', 'thememenuitem_hover', 'themesubmenu', 'icon_small_theme');
	uasort($COLOR_THEME_LIST, 'utf8_strcasecmp');
	foreach ($COLOR_THEME_LIST as $colorChoice=>$colorName) {
		$submenu=new WT_Menu($colorName, get_query_url(array('themecolor'=>$colorChoice)));
		$menu->addSubMenu($submenu);
	}
	return '<div class="color_form">'.$menu->getMenuAsDropdown().'</div>';
}

/**
 *  Define the default palette to be used.  Set $subColor
 *  to one of the collowing values to determine the default:
 *
 */

$COLOR_THEME_LIST=array(
	'aquamarine'      => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Aqua Marine'),
	'ash'             => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Ash'),
	'belgianchocolate'=> /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Belgian Chocolate'),
	'bluelagoon'      => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Blue Lagoon'),
	'bluemarine'      => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Blue Marine'),
	'coldday'         => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Cold Day'),
	'greenbeam'       => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Green Beam'),
	'mediterranio'    => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Mediterranio'),
	'mercury'         => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Mercury'),
	'nocturnal'       => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Nocturnal'),
	'olivia'          => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Olivia'),
	'pinkplastic'     => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Pink Plastic'),
	'shinytomato'     => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Shiny Tomato'),
	'tealtop'         => /* I18N: This is the name of theme color-scheme */ WT_I18N::translate('Teal Top'),
);

if (isset($_GET['themecolor']) && array_key_exists($_GET['themecolor'], $COLOR_THEME_LIST)) {
	// Request to change color
	$subColor=$_GET['themecolor'];
	if (WT_USER_ID) {
		set_user_setting(WT_USER_ID, 'themecolor', $subColor);
		set_site_setting('DEFAULT_COLOR_PALETTE', $subColor);
	}
	unset($_GET['themecolor']);
} elseif (isset($_SESSION['themecolor']))  {
	// Previously selected color
	$subColor=$_SESSION['themecolor'];
} else {
	if (WT_USER_ID) {
		$subColor=get_user_setting(WT_USER_ID, 'themecolor');
		if (!array_key_exists($subColor, $COLOR_THEME_LIST)) {
			$subColor = get_site_setting('DEFAULT_COLOR_PALETTE','ash');
		}
	} else {
		$subColor=get_site_setting('DEFAULT_COLOR_PALETTE','ash');
	}
}

$_SESSION['themecolor']=$subColor;

$theme_name       = "colors"; // need double quotes, as file is scanned/parsed by script
$footerfile       = WT_THEME_DIR . 'footer.php';
$headerfile       = WT_THEME_DIR . 'header.php';
$modules          = WT_THEME_DIR . 'modules.css';
$print_stylesheet = WT_THEME_DIR . 'print.css';
$rtl_stylesheet   = WT_THEME_DIR . 'style_rtl.css';
$stylesheet       = WT_THEME_DIR . 'css/' . $subColor . '.css';
$WT_MENU_LOCATION = 'top';
$WT_USE_HELPIMG   = true;

$WT_IMAGES=array(
	'add'=>WT_THEME_DIR.'images/add.gif',
	'admin'=>WT_THEME_DIR.'images/admin.gif',
	'ancestry'=>WT_THEME_DIR.'images/ancestry.gif',
	'calendar'=>WT_THEME_DIR.'images/calendar.gif',
	'center'=>WT_THEME_DIR.'images/center.gif',
	'cfamily'=>WT_THEME_DIR.'images/cfamily.gif',
	'charts'=>WT_THEME_DIR.'images/charts.gif',
	'childless'=>WT_THEME_DIR.'images/childless.gif',
	'children'=>WT_THEME_DIR.'images/children.gif',
	'clippings'=>WT_THEME_DIR.'images/clippings.gif',
	'darrow'=>WT_THEME_DIR.'images/darrow.gif',
	'darrow2'=>WT_THEME_DIR.'images/darrow2.gif',
	'ddarrow'=>WT_THEME_DIR.'images/ddarrow.gif',
	'default_image_F'=>WT_THEME_DIR.'images/silhouette_female.png',
	'default_image_M'=>WT_THEME_DIR.'images/silhouette_male.png',
	'default_image_U'=>WT_THEME_DIR.'images/silhouette_unknown.png',
	'descendant'=>WT_THEME_DIR.'images/descendancy.gif',
	'dline'=>WT_THEME_DIR.'images/dline.gif',
	'dline2'=>WT_THEME_DIR.'images/dline2.gif',
	'edit_fam'=>WT_THEME_DIR.'images/edit_fam.gif',
	'edit_indi'=>WT_THEME_DIR.'images/edit_indi.gif',
	'edit_media'=>WT_THEME_DIR.'images/edit_media.gif',
	'edit_note'=>WT_THEME_DIR.'images/edit_note.gif',
	'edit_repo'=>WT_THEME_DIR.'images/edit_repo.gif',
	'edit_sour'=>WT_THEME_DIR.'images/edit_sour.gif',
	'fambook'=>WT_THEME_DIR.'images/fambook.gif',
	'fanchart'=>WT_THEME_DIR.'images/fanchart.gif',
	'favorites'=>WT_THEME_DIR.'images/favorites.gif',
	'fscreen'=>WT_THEME_DIR.'images/fscreen.gif',
	'gedcom'=>WT_THEME_DIR.'images/gedcom.gif',
	'help'=>WT_THEME_DIR.'images/help.gif',
	'hline'=>WT_THEME_DIR.'images/hline.gif',
	'home'=>WT_THEME_DIR.'images/home.gif',
	'hourglass'=>WT_THEME_DIR.'images/hourglass.gif',
	'indis'=>WT_THEME_DIR.'images/indis.gif',
	'itree'=>WT_THEME_DIR.'images/itree.gif',
	'larrow'=>WT_THEME_DIR.'images/larrow.gif',
	'larrow2'=>WT_THEME_DIR.'images/larrow2.gif',
	'ldarrow'=>WT_THEME_DIR.'images/ldarrow.gif',
	'lists'=>WT_THEME_DIR.'images/lists.gif',

	// - lifespan chart arrows
	'lsdnarrow'=>WT_THEME_DIR.'images/lifespan-down.png',
	'lsltarrow'=>WT_THEME_DIR.'images/lifespan-left.png',
	'lsrtarrow'=>WT_THEME_DIR.'images/lifespan-right.png',
	'lsuparrow'=>WT_THEME_DIR.'images/lifespan-up.png',

	'media'=>WT_THEME_DIR.'images/media.gif',
	'menu_help'=>WT_THEME_DIR.'images/menu_help.gif',
	'menu_media'=>WT_THEME_DIR.'images/menu_media.gif',
	'menu_note'=>WT_THEME_DIR.'images/menu_note.gif',
	'menu_repository'=>WT_THEME_DIR.'images/menu_repository.gif',
	'menu_source'=>WT_THEME_DIR.'images/menu_source.gif',
	'minus'=>WT_THEME_DIR.'images/minus.gif',
	'mypage'=>WT_THEME_DIR.'images/mypage.gif',
	'note'=>WT_THEME_DIR.'images/notes.gif',
	'patriarch'=>WT_THEME_DIR.'images/patriarch.gif',
	'pedigree'=>WT_THEME_DIR.'images/pedigree.gif',
	'place'=>WT_THEME_DIR.'images/place.gif',
	'plus'=>WT_THEME_DIR.'images/plus.gif',
	'rarrow'=>WT_THEME_DIR.'images/rarrow.gif',
	'rarrow2'=>WT_THEME_DIR.'images/rarrow2.gif',
	'rdarrow'=>WT_THEME_DIR.'images/rdarrow.gif',
	'relationship'=>WT_THEME_DIR.'images/relationship.gif',
	'reminder'=>WT_THEME_DIR.'images/reminder.gif',
	'remove'=>WT_THEME_DIR.'images/delete.png',
	'reports'=>WT_THEME_DIR.'images/report.gif',
	'repository'=>WT_THEME_DIR.'images/repository.gif',
	'rings'=>WT_THEME_DIR.'images/rings.gif',
	'search'=>WT_THEME_DIR.'images/search.gif',
	'selected'=>WT_THEME_DIR.'images/selected.png',
	'sex_f_15x15'=>WT_THEME_DIR.'images/sex_f_15x15.gif',
	'sex_f_9x9'=>WT_THEME_DIR.'images/sex_f_9x9.gif',
	'sex_m_15x15'=>WT_THEME_DIR.'images/sex_m_15x15.gif',
	'sex_m_9x9'=>WT_THEME_DIR.'images/sex_m_9x9.gif',
	'sex_u_15x15'=>WT_THEME_DIR.'images/sex_u_15x15.gif',
	'sex_u_9x9'=>WT_THEME_DIR.'images/sex_u_9x9.gif',
	'sfamily'=>WT_THEME_DIR.'images/sfamily.gif',
	'source'=>WT_THEME_DIR.'images/source.gif',
	'spacer'=>WT_THEME_DIR.'images/spacer.gif',
	'statistic'=>WT_THEME_DIR.'images/statistic.gif',
	'stop'=>WT_THEME_DIR.'images/stop.gif',
	'target'=>WT_THEME_DIR.'images/buttons/target.gif',
	'timeline'=>WT_THEME_DIR.'images/timeline.gif',
	'tree'=>WT_THEME_DIR.'images/gedcom.gif',
	'uarrow'=>WT_THEME_DIR.'images/uarrow.gif',
	'uarrow2'=>WT_THEME_DIR.'images/uarrow2.gif',
	'udarrow'=>WT_THEME_DIR.'images/udarrow.gif',
	'vline'=>WT_THEME_DIR.'images/vline.gif',
	'warning'=>WT_THEME_DIR.'images/warning.gif',
	'webtrees'=>WT_THEME_DIR.'images/webtrees.png',
	'wiki'=>WT_THEME_DIR.'images/wiki.png',
	'zoomin'=>WT_THEME_DIR.'images/zoomin.gif',
	'zoomout'=>WT_THEME_DIR.'images/zoomout.gif',

	//- buttons for data entry pages
	'button_addmedia'=>WT_THEME_DIR.'images/buttons/addmedia.gif',
	'button_addnote'=>WT_THEME_DIR.'images/buttons/addnote.gif',
	'button_addrepository'=>WT_THEME_DIR.'images/buttons/addrepository.gif',
	'button_addsource'=>WT_THEME_DIR.'images/buttons/addsource.gif',
	'button_autocomplete'=>WT_THEME_DIR.'images/buttons/autocomplete.gif',
	'button_calendar'=>WT_THEME_DIR.'images/buttons/calendar.gif',
	'button_family'=>WT_THEME_DIR.'images/buttons/family.gif',
	'button_find_facts'=>WT_THEME_DIR.'images/buttons/find_facts.png',
	'button_head'=>WT_THEME_DIR.'images/buttons/head.gif',
	'button_indi'=>WT_THEME_DIR.'images/buttons/indi.gif',
	'button_keyboard'=>WT_THEME_DIR.'images/buttons/keyboard.gif',
	'button_media'=>WT_THEME_DIR.'images/buttons/media.gif',
	'button_note'=>WT_THEME_DIR.'images/buttons/note.gif',
	'button_place'=>WT_THEME_DIR.'images/buttons/place.gif',
	'button_repository'=>WT_THEME_DIR.'images/buttons/repository.gif',
	'button_source'=>WT_THEME_DIR.'images/buttons/source.gif',

	// media images
	'media_audio'=>WT_THEME_DIR.'images/media/audio.png',
	'media_doc'=>WT_THEME_DIR.'images/media/doc.gif',
	'media_flash'=>WT_THEME_DIR.'images/media/flash.png',
	'media_flashrem'=>WT_THEME_DIR.'images/media/flashrem.png',
	'media_ged'=>WT_THEME_DIR.'images/media/ged.gif',
	'media_globe'=>WT_THEME_DIR.'images/media/globe.png',
	'media_html'=>WT_THEME_DIR.'images/media/html.gif',
	'media_pdf'=>WT_THEME_DIR.'images/media/pdf.gif',
	'media_picasa'=>WT_THEME_DIR.'images/media/picasa.png',
	'media_tex'=>WT_THEME_DIR.'images/media/tex.gif',
	'media_wmv'=>WT_THEME_DIR.'images/media/wmv.png',
	'media_wmvrem'=>WT_THEME_DIR.'images/media/wmvrem.png',
);

//-- Variables for the Fan chart
$fanChart = array(
	'font' => WT_ROOT.'includes/fonts/DejaVuSans.ttf',
	'size' => '7px',
	'color' => '#000000',
	'bgColor' => '#eeeeee',
	'bgMColor' => '#b1cff0',
	'bgFColor' => '#e9daf1'
);

//-- This section defines variables for the pedigree chart
$bwidth = 225; // -- width of boxes on pedigree chart
$bheight = 80; // -- height of boxes on pedigree chart
$baseyoffset = 10; // -- position the entire pedigree tree relative to the top of the page
$basexoffset = 10; // -- position the entire pedigree tree relative to the left of the page
$bxspacing = 4; // -- horizontal spacing between boxes on the pedigree chart
$byspacing = 5; // -- vertical spacing between boxes on the pedigree chart
$brborder = 1; // -- box right border thickness

// -- global variables for the descendancy chart
$Dbaseyoffset = 20; // -- position the entire descendancy tree relative to the top of the page
$Dbasexoffset = 20; // -- position the entire descendancy tree relative to the left of the page
$Dbxspacing = 0; // -- horizontal spacing between boxes
$Dbyspacing = 10; // -- vertical spacing between boxes
$Dbwidth = 250; // -- width of DIV layer boxes
$Dbheight = 78; // -- height of DIV layer boxes
$Dindent = 15; // -- width to indent descendancy boxes
$Darrowwidth = 30; // -- additional width to include for the up arrows

$CHARTS_CLOSE_HTML = true;                //-- should the charts, pedigree, descendacy, etc close the HTML on the page

// --  The largest possible area for charts is 300,000 pixels. As the maximum height or width is 1000 pixels
$WT_STATS_S_CHART_X = "440";
$WT_STATS_S_CHART_Y = "125";
$WT_STATS_L_CHART_X = "900";
// --  For map charts, the maximum size is 440 pixels wide by 220 pixels high
$WT_STATS_MAP_X = "440";
$WT_STATS_MAP_Y = "220";

$WT_STATS_CHART_COLOR1 = "ffffff";
$WT_STATS_CHART_COLOR2 = "95b8e0";
$WT_STATS_CHART_COLOR3 = "c8e7ff";
