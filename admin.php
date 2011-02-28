<?php
/**
 * Welcome page for the administration module
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2010 webtrees development team.
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
 * @subpackage Admin
 * @version $Id$
 */

define('WT_SCRIPT_NAME', 'admin.php');

require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

// Only managers can access this page
if (!WT_USER_GEDCOM_ADMIN) {
	// TODO: Check if we are a manager in *any* gedcom, not just the current one
	header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH.'login.php?url='.WT_SCRIPT_NAME);
	exit;
}

print_header(WT_I18N::translate('Administration'));

// Check for updates
$latest_version_txt=fetch_latest_version();
if ($latest_version_txt) {
	list($latest_version, $earliest_version, $download_url)=explode('|', $latest_version_txt);
} else {
	// Cannot determine the latest version
	$latest_version='';
}

// Load all available gedcoms
$all_gedcoms = get_all_gedcoms();

$stats=new WT_Stats(WT_GEDCOM);
	$totusers  =0;       // Total number of users
	$warnusers =0;       // Users with warning
	$applusers =0;       // Users who have not verified themselves
	$nverusers =0;       // Users not verified by admin but verified themselves
	$adminusers=0;       // Administrators
	$userlang  =array(); // Array for user languages
	$gedadmin  =array(); // Array for managers
	
// Display a series of "blocks" of general information, vary according to admin or manager.

echo '<div id="content_container">';

echo '<div id="x">';// div x - manages the accordion effect

echo '<h2>', WT_WEBTREES, ' ', WT_VERSION, '</h2>',
	'<div id="about">',
	'<p>', WT_I18N::translate('These pages provide access to all the configuration settings and management tools for this <b>webtrees</b> site.'), '</p>',
	'<p>',  /* I18N: %s is a URL/link to the project website */ WT_I18N::translate('Support and documentation can be found at %s.', ' <a class="current" href="http://webtrees.net/">webtrees.net</a>'), '</p>',
	'</div>';

// Accordion block for UPGRADE - only shown when upgrades are available
if (WT_USER_IS_ADMIN && $latest_version && version_compare(WT_VERSION, $latest_version)<0) {
	echo
		'<h2><span class="warning">',
		/* I18N: %s is a version number */ WT_I18N::translate('Upgrade to webtrees %s', $latest_version),
		'</span></h2>',
		'<div>',
		'<h3>', WT_I18N::translate('Upgrade instructions'), '</h3>',
		'<ul>',
		'<li>', /* I18N: %s is a URL/link to a .ZIP file */ WT_I18n::translate('Download %s and extract the files.', '<a class="current" href="'.$download_url.'">'.basename($download_url).'</a>'), '</li>';
	if (version_compare(WT_VERSION, $earliest_version)<0) {
		echo '<li>', WT_I18N::translate('Accept or reject any pending changes.'), '</li>';
		echo '<li>', WT_I18N::translate('Save all your family trees to disk, by using the "export" function for each one.'), '</li>';
	}

	echo '<li>', WT_I18N::translate('Copy the new files to the web server, replacing any that have the same name.'), '</li>';

	if (version_compare(WT_VERSION, $earliest_version)<0) {
		echo '<li>', WT_I18N::translate('Load all your family trees from disk, by using the "import" function for each one.'), '</li>';
	}

	echo
		'</ul>',
		'<h3>', WT_I18N::translate('Recommendations'), '</h3>',
		'<ul>',
		'<li>', WT_I18N::translate('Make a backup of your database before you start.'), '</li>',
		'<li>', /* I18N: %s is a filename */ WT_I18N::translate('Take your site offline while copying the new files.  Do this by temporarily creating a file %s on the web server.', '<tt style="white-space:nowrap">'.WT_ROOT.'data/offline.txt'.'</tt>'), '</li>',
		'</ul>',
		'</div>';
}

// Accordion block for DELETE OLD FILES - only shown when old files are found
$old_files_found=false;
foreach (old_paths() as $path) {
	if (file_exists($path)) {
		delete_recursively($path);
		// we may not have permission to delete.  Is it still there?
		if (file_exists($path)) {
			$old_files_found=true;
		}
	}
}

if (WT_USER_IS_ADMIN && $old_files_found) {
	echo
		'<h2><span class="warning">', WT_I18N::translate('Old files found', $latest_version), '</span></h2>',
		'<div>',
		'<p>', WT_I18N::translate('Files have been found from a previous version of webtrees.  Old files can sometimes be a security risk.  You should delete them.'), '</p>',
		'<ul>';
		foreach (old_paths() as $path) {
			if (file_exists($path)) {
				echo '<li>', $path, '</li>';
			}
		}
	echo
		'</ul>',
		'</div>';
}

echo
	'<h2>', WT_I18N::translate('Users'), '</h2>',
	'<div id="users">'; //id = users

		foreach(get_all_users() as $user_id=>$user_name) {
			$totusers = $totusers + 1;
			if (((date("U") - (int)get_user_setting($user_id, 'reg_timestamp')) > 604800) && !get_user_setting($user_id, 'verified')) {
				$warnusers++;
			}
			if (!get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'verified')) {
				$nverusers++;
			}
			if (!get_user_setting($user_id, 'verified')) {
				$applusers++;
			}
			if (get_user_setting($user_id, 'canadmin')) {
				$adminusers++;
			}
			foreach ($all_gedcoms as $ged_id=>$ged_name) {
				if (get_user_gedcom_setting($user_id, $ged_id, 'canedit')=='admin') {
					$title=PrintReady(strip_tags(get_gedcom_setting($ged_id, 'title')));
					if (isset($gedadmin[$title])) {
						$gedadmin[$title]["number"]++;
					} else {
						$gedadmin[$title]["name"] = $title;
						$gedadmin[$title]["number"] = 1;
						$gedadmin[$title]["ged"] = $ged_name;
					}
				}
			}
			if ($user_lang=get_user_setting($user_id, 'language')) {
				if (isset($userlang[$user_lang]))
					$userlang[$user_lang]["number"]++;
				else {
					$userlang[$user_lang]["langname"] = Zend_Locale::getTranslation($user_lang, 'language', WT_LOCALE);
					$userlang[$user_lang]["number"] = 1;
				}
			}
		}	

	echo
		'<table>',
		'<tr><td>', WT_I18N::translate('Total number of users'), '</td><td>', $totusers, '</td></tr>',
		'<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="admin_users.php?action=listusers&amp;filter=adminusers">', WT_I18N::translate('Administrators'), '</a></td><td>', $adminusers, '</td></tr>',
		'<tr><td colspan="2">', WT_I18N::translate('Managers'), '</td></tr>';
		foreach ($gedadmin as $key=>$geds) {
			echo '<tr><td><div><a href="admin_users.php?action=listusers&amp;filter=gedadmin&amp;ged='.rawurlencode($geds['ged']), '">', $geds['name'], '</a></div></td><td>', $geds['number'], '</td></tr>';
		}
	echo '<tr><td>';
	if ($warnusers == 0) {
		echo WT_I18N::translate('Users with warnings');
	} else {
		echo '<a href="admin_users.php?action=listusers&amp;filter=warnings">', WT_I18N::translate('Users with warnings'), '</a>';
	}
	echo '</td><td>', $warnusers, '</td></tr><tr><td>';
	if ($applusers == 0) {
		echo WT_I18N::translate('Unverified by User');
	} else {
		echo '<a href="admin_users.php?action=listusers&amp;filter=usunver">', WT_I18N::translate('Unverified by User'), '</a>';
	}
	echo '</td><td>', $applusers, '</td></tr><tr><td>';
	if ($nverusers == 0) {
		echo WT_I18N::translate('Unverified by Administrator');
	} else {
		echo '<a href="admin_users.php?action=listusers&amp;filter=admunver">', WT_I18N::translate('Unverified by Administrator'), '</a>';
	}
	echo '</td><td>', $nverusers, '</td></tr>';
	echo '<tr><td colspan="2">', WT_I18N::translate('Users\' languages'), '</td></tr>';
	foreach ($userlang as $key=>$ulang) {
		echo '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<a href="admin_users.php?action=listusers&amp;filter=language&amp;usrlang=', $key, '">', $ulang['langname'], '</a></td><td>', $ulang['number'], '</td></tr>';
	}
	echo
		'</tr>',
		'<tr><td colspan="2">', WT_I18N::translate('Users Logged In'), '</td></tr>',
		'<tr><td colspan="2"><div>', $stats->_usersLoggedIn('list'), '</div></td></tr>',
		'</table>';
echo '</div>'; // id = users

echo
	'<h2>', WT_I18N::translate('Family trees'), '</h2>',
	'<div id="trees">',// id=trees
	'<div id="tree_stats">';
$n=0;
foreach ($all_gedcoms as $ged_id=>$gedcom) {
	$stats = new WT_Stats($gedcom);
	if ($ged_id==WT_GED_ID) {
		$accordion_element=$n;
	}
	++$n;
	echo
		'<h3>', $stats->gedcomTitle(), '</h3>',
		'<div>',
		'<table>',
		'<tr><td>&nbsp;</td><td><span>', WT_I18N::translate('Count'), '</span></td></tr>',
		'<tr><th><a href="indilist.php?ged=',  rawurlencode($gedcom), '">',
		WT_I18N::translate('Individuals'), '</a></th><td>', $stats->totalIndividuals(),
		'</td></tr>',
		'<tr><th><a href="famlist.php?ged=',   rawurlencode($gedcom), '">',
		WT_I18N::translate('Families'), '</a></th><td>', $stats->totalFamilies(),
		'</td></tr>',
		'<tr><th><a href="sourlist.php?ged=',  rawurlencode($gedcom), '">',
		WT_I18N::translate('Sources'), '</a></th><td>', $stats->totalSources(),
		'</td></tr>',
		'<tr><th><a href="repolist.php?ged=',  rawurlencode($gedcom), '">',
		WT_I18N::translate('Repositories'), '</a></th><td>', $stats->totalRepositories(),
		'</td></tr>',
		'<tr><th><a href="medialist.php?ged=', rawurlencode($gedcom), '">',
		WT_I18N::translate('Media objects'), '</a></th><td>', $stats->totalMedia(),
		'</td></tr>',
		'<tr><th><a href="notelist.php?ged=',  rawurlencode($gedcom), '">',
		WT_I18N::translate('Notes'), '</a></th><td>', $stats->totalNotes(),
		'</td></tr>',
		'</table>',
		'</div>';
}
echo
	'</div>', // id=tree_stats
	WT_JS_START,
	'jQuery("#tree_stats").accordion({active:',$accordion_element,', icons:false});',
	WT_JS_END,
	'</div>'; // id=trees

echo
	'<h2>', WT_I18N::translate('Recent changes'), '</h2>',
	'<div id="recent2">'; //id=recent
	echo
	'<div id="changes">';
$n=0;
foreach ($all_gedcoms as $ged_id=>$gedcom) {
	if ($ged_id==WT_GED_ID) {
		$accordion_element=$n;
	}
	++$n;
	echo 
		'<h3>', get_gedcom_setting($ged_id, 'title'), '</h3>',
		'<div>',
		'<table>',
		'<tr><td>&nbsp;</td><td><span>', WT_I18N::translate('Day'), '</span></td><td><span>', WT_I18N::translate('Week'), '</span></td><td><span>', WT_I18N::translate('Month'), '</span></td></tr>',
		'<tr><th>', WT_I18N::translate('Individuals'), '</th><td>', count_changes_today($GEDCOM_ID_PREFIX, $ged_id), '</td><td>', count_changes_week($GEDCOM_ID_PREFIX, $ged_id), '</td><td>', count_changes_month($GEDCOM_ID_PREFIX, $ged_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Families'), '</th><td>', count_changes_today($FAM_ID_PREFIX, $ged_id), '</td><td>', count_changes_week($FAM_ID_PREFIX, $ged_id), '</td><td>', count_changes_month($FAM_ID_PREFIX, $ged_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Sources'), '</th><td>', count_changes_today($SOURCE_ID_PREFIX, $ged_id), '</td><td>', count_changes_week($SOURCE_ID_PREFIX, $ged_id), '</td><td>', count_changes_month($SOURCE_ID_PREFIX, $ged_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Repositories'), '</th><td>', count_changes_today($REPO_ID_PREFIX, $ged_id), '</td><td>', count_changes_week($REPO_ID_PREFIX, $ged_id), '</td><td>', count_changes_month($REPO_ID_PREFIX, $ged_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Media objects'), '</th><td>', count_changes_today($MEDIA_ID_PREFIX, $ged_id), '</td><td>', count_changes_week($MEDIA_ID_PREFIX, $ged_id), '</td><td>', count_changes_month($MEDIA_ID_PREFIX, $ged_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Notes'), '</th><td>', count_changes_today($NOTE_ID_PREFIX, $ged_id), '</td><td>', count_changes_week($NOTE_ID_PREFIX, $ged_id), '</td><td>', count_changes_month($NOTE_ID_PREFIX, $ged_id), '</td></tr>',
		'</table>',
		'</div>';
	}
echo
	'</div>', // id=changes
	WT_JS_START,
	'jQuery("#changes").accordion({active:',$accordion_element,', icons:false});',
	WT_JS_END,
	'</div>'; // id=recent

echo
	'</div>', //id = "x"
	WT_JS_START,
	'jQuery("#x").accordion({active:0, icons:false});',
	WT_JS_END,
	'</div>'; //id = content_container

print_footer();

// This is a list of old files and directories, from earlier versions of webtrees, that can be deleted
function old_paths() {
	return array(
		// Removed in 1.0.2
		WT_ROOT.'language/en.mo',
		// Removed in 1.0.3
		WT_ROOT.'themechange.php',
		// Removed in 1.0.4
		WT_ROOT.'themes/fab/images/notes.gif',
		// Removed in 1.0.5
		WT_ROOT.'modules/lightbox/functions/lb_indi_doors_0.php',
		WT_ROOT.'modules/lightbox/functions/lb_indi_doors_1.php',
		WT_ROOT.'modules/lightbox/functions/lb_indi_tabs_0.php',
		WT_ROOT.'modules/lightbox/functions/lb_indi_tabs_1.php',
		// Removed in 1.0.6
		WT_ROOT.'includes/extras',
		// Removed in 1.1.0
		WT_ROOT.'addremotelink.php',
		WT_ROOT.'addsearchlink.php',
		WT_ROOT.'client.php',
		WT_ROOT.'dir_editor.php',
		WT_ROOT.'editconfig_gedcom.php',
		WT_ROOT.'editgedcoms.php',
		WT_ROOT.'edit_merge.php',
		WT_ROOT.'genservice.php',
		WT_ROOT.'images/buttons',
		WT_ROOT.'images/checked.gif',
		WT_ROOT.'images/checked_qm.gif',
		WT_ROOT.'images/children.gif',
		WT_ROOT.'images/feed-icon16x16.png',
		WT_ROOT.'images/forbidden.gif',
		WT_ROOT.'images/media',
		WT_ROOT.'images/reminder.gif',
		WT_ROOT.'images/selected.png',
		WT_ROOT.'images/sex_f_15x15.gif',
		WT_ROOT.'images/sex_f_9x9.gif',
		WT_ROOT.'images/sex_m_15x15.gif',
		WT_ROOT.'images/sex_m_9x9.gif',
		WT_ROOT.'images/sex_u_15x15.gif',
		WT_ROOT.'images/sex_u_9x9.gif',
		WT_ROOT.'images/small',
		WT_ROOT.'images/trashcan.gif',
		WT_ROOT.'images/warning.gif',
		WT_ROOT.'includes/classes',
		WT_ROOT.'includes/controllers',
		WT_ROOT.'includes/family_nav.php',
		WT_ROOT.'includes/functions/functions_lang.php',
		WT_ROOT.'includes/functions/functions_tools.php',
		WT_ROOT.'js/conio',
		WT_ROOT.'logs.php',
		WT_ROOT.'manageservers.php',
		WT_ROOT.'media.php',
		WT_ROOT.'module_admin.php',
		WT_ROOT.'modules/address_label_report',
		WT_ROOT.'modules/batch_update/batch_update.php',
		WT_ROOT.'modules/batch_update/plugins/birth_y.php',
		WT_ROOT.'modules/batch_update/plugins/tmglatlon.php',
		WT_ROOT.'modules/googlemap/editconfig.php',
		WT_ROOT.'modules/googlemap/placecheck.php',
		WT_ROOT.'modules/googlemap/places.php',
		WT_ROOT.'modules/googlemap/wt_googlemap.js',
		WT_ROOT.'modules/lightbox/lb_editconfig.php',
		WT_ROOT.'modules/pedigree_report/report_landscape.xml',
		WT_ROOT.'modules/pedigree_report/report_portrait.xml',
		WT_ROOT.'modules/sitemap/admin_config.php',
		WT_ROOT.'modules/sitemap/gss.xsl',
		WT_ROOT.'modules/sitemap/index.php',
		WT_ROOT.'modules/sitemap/sortdown.gif',
		WT_ROOT.'modules/sitemap/sortup.gif',
		WT_ROOT.'opensearch.php',
		WT_ROOT.'PEAR.php',
		WT_ROOT.'pgv_to_wt.php',
		WT_ROOT.'places',
		//WT_ROOT.'robots.txt', // Do not delete this - it may contain user data
		WT_ROOT.'serviceClientTest.php',
		WT_ROOT.'siteconfig.php',
		WT_ROOT.'SOAP',
		WT_ROOT.'themes/clouds/images/xml.gif',
		WT_ROOT.'themes/clouds/mozilla.css',
		WT_ROOT.'themes/clouds/netscape.css',
		WT_ROOT.'themes/colors/images/xml.gif',
		WT_ROOT.'themes/colors/mozilla.css',
		WT_ROOT.'themes/colors/netscape.css',
		WT_ROOT.'themes/fab/images/checked.gif',
		WT_ROOT.'themes/fab/images/checked_qm.gif',
		WT_ROOT.'themes/fab/images/feed-icon16x16.png',
		WT_ROOT.'themes/fab/images/menu_punbb.gif',
		WT_ROOT.'themes/fab/images/trashcan.gif',
		WT_ROOT.'themes/fab/images/xml.gif',
		WT_ROOT.'themes/fab/mozilla.css',
		WT_ROOT.'themes/fab/netscape.css',
		WT_ROOT.'themes/minimal/mozilla.css',
		WT_ROOT.'themes/minimal/netscape.css',
		WT_ROOT.'themes/webtrees/images/checked.gif',
		WT_ROOT.'themes/webtrees/images/checked_qm.gif',
		WT_ROOT.'themes/webtrees/images/feed-icon16x16.png',
		WT_ROOT.'themes/webtrees/images/header.jpg',
		WT_ROOT.'themes/webtrees/images/trashcan.gif',
		WT_ROOT.'themes/webtrees/images/xml.gif',
		WT_ROOT.'themes/webtrees/mozilla.css',
		WT_ROOT.'themes/webtrees/netscape.css',
		WT_ROOT.'themes/webtrees/style_rtl.css',
		WT_ROOT.'themes/xenea/mozilla.css',
		WT_ROOT.'themes/xenea/netscape.css',
		WT_ROOT.'uploadmedia.php',
		WT_ROOT.'useradmin.php',
		WT_ROOT.'webservice',
		WT_ROOT.'wtinfo.php',
		// ...this list is complete, up to svn 10930, and was generated with the help of
		//
		// svn diff svn://svn.webtrees.net/trunk@9997 svn://svn.webtrees.net/trunk --summarize | grep ^D | sort
	);
}

// Delete a file or directory, ignoring errors
function delete_recursively($path) {
	@chmod($path, 0777);
	if (is_dir($path)) {
		$dir=opendir($path);
		while ($dir!==false && (($file=readdir($dir))!==false)) {
			if ($file!='.' && $file!='..') {
				delete_recursively($path.'/'.$file);
			}
		}
		closedir($dir);
		@rmdir($path);
	} else {
		@unlink($path);
	}
}
