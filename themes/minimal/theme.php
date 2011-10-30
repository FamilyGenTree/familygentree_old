<?php
// Minimal theme
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team.  All rights reserved.
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

$theme_name       = "minimal"; // need double quotes, as file is scanned/parsed by script
$stylesheet       = WT_THEME_URL.'style.css';
$print_stylesheet = WT_THEME_URL.'print.css';
$headerfile       = WT_THEME_DIR.'header.php';
$footerfile       = WT_THEME_DIR.'footer.php';
$WT_USE_HELPIMG   = false;

//-- variables for image names
$WT_IMAGES=array(
	'add'            =>WT_THEME_URL.'images/add.png',
	'admin'          =>WT_THEME_URL.'images/admin.png',
	'ancestry'       =>WT_THEME_URL.'images/ancestry.png',
	'calendar'       =>WT_THEME_URL.'images/calendar.png',
	'center'         =>WT_THEME_URL.'images/center.png',
	'cfamily'        =>WT_THEME_URL.'images/cfamily.png',
	'childless'      =>WT_THEME_URL.'images/childless.png',
	'children'       =>WT_THEME_URL.'images/children.png',
	'clippings'      =>WT_THEME_URL.'images/clippings.png',
	'darrow'         =>WT_THEME_URL.'images/darrow.png',
	'darrow2'        =>WT_THEME_URL.'images/darrow2.png',
	'ddarrow'        =>WT_THEME_URL.'images/ddarrow.png',
	'default_image_F'=>WT_THEME_URL.'images/silhouette_female.png',
	'default_image_M'=>WT_THEME_URL.'images/silhouette_male.png',
	'default_image_U'=>WT_THEME_URL.'images/silhouette_unknown.png',
	'descendant'     =>WT_THEME_URL.'images/descendancy.png',
	'dline'          =>WT_THEME_URL.'images/dline.png',
	'dline2'         =>WT_THEME_URL.'images/dline2.png',
	'fambook'        =>WT_THEME_URL.'images/fambook.png',
	'fanchart'       =>WT_THEME_URL.'images/fanchart.png',
	'fscreen'        =>WT_THEME_URL.'images/fscreen.png',
	'gedcom'         =>WT_THEME_URL.'images/gedcom.png',
	'help'           =>WT_THEME_URL.'images/help.png',
	'hline'          =>WT_THEME_URL.'images/hline.png',
	'indis'          =>WT_THEME_URL.'images/indis.png',
	'itree'          =>WT_THEME_URL.'images/itree.png',
	'larrow'         =>WT_THEME_URL.'images/larrow.png',
	'larrow2'        =>WT_THEME_URL.'images/larrow2.png',
	'ldarrow'        =>WT_THEME_URL.'images/ldarrow.png',
	'lsdnarrow'      =>WT_THEME_URL.'images/lifespan-down.png',
	'lsltarrow'      =>WT_THEME_URL.'images/lifespan-left.png',
	'lsrtarrow'      =>WT_THEME_URL.'images/lifespan-right.png',
	'lsuparrow'      =>WT_THEME_URL.'images/lifespan-up.png',
	'media'          =>WT_THEME_URL.'images/media.png',
	'minus'          =>WT_THEME_URL.'images/minus.png',
	'mypage'         =>WT_THEME_URL.'images/mypage.png',
	'note'           =>WT_THEME_URL.'images/notes.png',
	'patriarch'      =>WT_THEME_URL.'images/patriarch.png',
	'pedigree'       =>WT_THEME_URL.'images/pedigree.png',
	'place'          =>WT_THEME_URL.'images/place.png',
	'plus'           =>WT_THEME_URL.'images/plus.png',
	'rarrow'         =>WT_THEME_URL.'images/rarrow.png',
	'rarrow2'        =>WT_THEME_URL.'images/rarrow2.png',
	'rdarrow'        =>WT_THEME_URL.'images/rdarrow.png',
	'reminder'       =>WT_THEME_URL.'images/reminder.png',
	'remove'         =>WT_THEME_URL.'images/remove.png',
	'reports'        =>WT_THEME_URL.'images/report.png',
	'repository'     =>WT_THEME_URL.'images/repository.png',
	'rings'          =>WT_THEME_URL.'images/rings.png',
	'search'         =>WT_THEME_URL.'images/search.png',
	'selected'       =>WT_THEME_URL.'images/selected.png',
	'sex_f_15x15'    =>WT_THEME_URL.'images/sex_f_15x15.png',
	'sex_f_9x9'      =>WT_THEME_URL.'images/sex_f_9x9.png',
	'sex_m_15x15'    =>WT_THEME_URL.'images/sex_m_15x15.png',
	'sex_m_9x9'      =>WT_THEME_URL.'images/sex_m_9x9.png',
	'sex_u_15x15'    =>WT_THEME_URL.'images/sex_u_15x15.png',
	'sex_u_9x9'      =>WT_THEME_URL.'images/sex_u_9x9.png',
	'sfamily'        =>WT_THEME_URL.'images/sfamily.png',
	'source'         =>WT_THEME_URL.'images/source.png',
	'spacer'         =>WT_THEME_URL.'images/spacer.png',
	'stop'           =>WT_THEME_URL.'images/stop.png',
	'timeline'       =>WT_THEME_URL.'images/timeline.png',
	'tree'           =>WT_THEME_URL.'images/gedcom.png',
	'uarrow'         =>WT_THEME_URL.'images/uarrow.png',
	'uarrow2'        =>WT_THEME_URL.'images/uarrow2.png',
	'udarrow'        =>WT_THEME_URL.'images/udarrow.png',
	'vline'          =>WT_THEME_URL.'images/vline.png',
	'warning'        =>WT_THEME_URL.'images/warning.png',
	'webtrees'       =>WT_THEME_URL.'images/webtrees.png',
	'zoomin'         =>WT_THEME_URL.'images/zoomin.png',
	'zoomout'        =>WT_THEME_URL.'images/zoomout.png',

	//- buttons for data entry pages
	'button_addmedia'     =>WT_THEME_URL.'images/buttons/addmedia.png',
	'button_addnote'      =>WT_THEME_URL.'images/buttons/addnote.png',
	'button_addrepository'=>WT_THEME_URL.'images/buttons/addrepository.png',
	'button_addsource'    =>WT_THEME_URL.'images/buttons/addsource.png',
	'button_calendar'     =>WT_THEME_URL.'images/buttons/calendar.png',
	'button_family'       =>WT_THEME_URL.'images/buttons/family.png',
	'button_find_facts'   =>WT_THEME_URL.'images/buttons/find_facts.png',
	'button_head'         =>WT_THEME_URL.'images/buttons/head.png',
	'button_indi'         =>WT_THEME_URL.'images/buttons/indi.png',
	'button_keyboard'     =>WT_THEME_URL.'images/buttons/keyboard.png',
	'button_media'        =>WT_THEME_URL.'images/buttons/media.png',
	'button_note'         =>WT_THEME_URL.'images/buttons/note.png',
	'button_place'        =>WT_THEME_URL.'images/buttons/place.png',
	'button_repository'   =>WT_THEME_URL.'images/buttons/repository.png',
	'button_source'       =>WT_THEME_URL.'images/buttons/source.png',
	'target'              =>WT_THEME_URL.'images/buttons/target.png',
	
	// media images
	'media_audio'   =>WT_THEME_URL.'images/media/audio.png',
	'media_doc'     =>WT_THEME_URL.'images/media/doc.png',
	'media_flash'   =>WT_THEME_URL.'images/media/flash.png',
	'media_flashrem'=>WT_THEME_URL.'images/media/flashrem.png',
	'media_ged'     =>WT_THEME_URL.'images/media/ged.png',
	'media_globe'   =>WT_THEME_URL.'images/media/globe.png',
	'media_html'    =>WT_THEME_URL.'images/media/html.png',
	'media_pdf'     =>WT_THEME_URL.'images/media/pdf.png',
	'media_picasa'  =>WT_THEME_URL.'images/media/picasa.png',
	'media_tex'     =>WT_THEME_URL.'images/media/tex.png',
	'media_wmv'     =>WT_THEME_URL.'images/media/wmv.png',
	'media_wmvrem'  =>WT_THEME_URL.'images/media/wmvrem.png',
);

//-- Variables for the Fan chart
$fanChart = array(
	'font' => WT_ROOT.'includes/fonts/DejaVuSans.ttf',
	'size' => '7px',
	'color' => '#000000',
	'bgColor' => '#eeeeee',
	'bgMColor' => '#cccccc',
	'bgFColor' => '#dddddd'
);

//-- This section defines variables for the pedigree chart
$bwidth = 240; // -- width of boxes on pedigree chart
$bheight = 80; // -- height of boxes on pedigree chart
$baseyoffset = 10; // -- position the entire pedigree tree relative to the top of the page
$basexoffset = 10; // -- position the entire pedigree tree relative to the left of the page
$bxspacing = 0; // -- horizontal spacing between boxes on the pedigree chart
$byspacing = 5; // -- vertical spacing between boxes on the pedigree chart
$brborder = 1; // -- box right border thickness

// -- global variables for the descendancy chart
$Dbaseyoffset = 0; // -- position the entire descendancy tree relative to the top of the page
$Dbasexoffset = 0; // -- position the entire descendancy tree relative to the left of the page
$Dbxspacing = 0; // -- horizontal spacing between boxes
$Dbyspacing = 1; // -- vertical spacing between boxes
$Dbwidth = 270; // -- width of DIV layer boxes
$Dbheight = 80; // -- height of DIV layer boxes
$Dindent = 15; // -- width to indent descendancy boxes
$Darrowwidth = 15; // -- additional width to include for the up arrows

$CHARTS_CLOSE_HTML = true; //-- should the charts, pedigree, descendacy, etc close the HTML on the page

// --  The largest possible area for charts is 300,000 pixels. As the maximum height or width is 1000 pixels
$WT_STATS_S_CHART_X = "440";
$WT_STATS_S_CHART_Y = "125";
$WT_STATS_L_CHART_X = "900";
// --  For map charts, the maximum size is 440 pixels wide by 220 pixels high
$WT_STATS_MAP_X = "440";
$WT_STATS_MAP_Y = "220";

$WT_STATS_CHART_COLOR1 = "ffffff";
$WT_STATS_CHART_COLOR2 = "555555";
$WT_STATS_CHART_COLOR3 = "cccccc";
