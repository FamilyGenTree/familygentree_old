<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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

class todo_WT_Module extends WT_Module implements WT_Module_Block {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Tasks that need further research.  */ WT_I18N::translate('Research tasks');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of "Research tasks" module */ WT_I18N::translate('A list of tasks and activities that are linked to the family tree.');
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $ctype, $WT_IMAGES, $controller;

		$show_unassigned=get_block_setting($block_id, 'show_unassigned', true);
		$show_other     =get_block_setting($block_id, 'show_other',      true);
		$show_future    =get_block_setting($block_id, 'show_future',     true);
		$block          =get_block_setting($block_id, 'block',           true);
		if ($cfg) {
			foreach (array('show_unassigned', 'show_other', 'show_future', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name=$cfg[$name];
				}
			}
		}

		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		if ($ctype=='gedcom' && WT_USER_GEDCOM_ADMIN || $ctype=='user' && WT_USER_ID) {
			$title='<img class="adminicon" src="'.$WT_IMAGES['admin'].'" width="15" height="15" alt="'.WT_I18N::translate('Configure').'"  onclick="window.open(\'index_edit.php?action=configure&amp;ctype='.$ctype.'&amp;block_id='.$block_id.'\', \'_blank\', \'top=50,left=50,width=600,height=350,scrollbars=1,resizable=1\');">';
		} else {
			$title='';
		}
		$title.=$this->getTitle().help_link('todo', $this->getName());

		$table_id = 'ID'.floor(microtime()*1000000); // create a unique ID
		$controller
			->addExternalJavaScript(WT_STATIC_URL.'js/jquery/jquery.dataTables.min.js')
			->addInlineJavaScript('
				jQuery("#'.$table_id.'").dataTable( {
				"sDom": \'t\',
				'.WT_I18N::datatablesI18N().',
				"bAutoWidth":false,
				"bPaginate": false,
				"bLengthChange": false,
				"bFilter": false,
				"bInfo": true,
				"bJQueryUI": true,
				"aoColumns": [
					/* 0-DATE */   		{ "bVisible": false },
					/* 1-Date */		{ "iDataSort": 0 },
					/* 1-Record */ 		{},
					/* 2-Username */	{},
					/* 3-Text */		{}
				]
				});		
			jQuery("#'.$table_id.'").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
			');
		$content='';
		$content .= '<div class="loading-image">&nbsp;</div>';
		$content .= '<table id="'.$table_id.'" style="visibility:hidden;">';
		$content .= '<thead><tr>';
		$content .= '<th>DATE</th>'; //hidden by datables code
		$content .= '<th>'.WT_Gedcom_Tag::getLabel('DATE').'</th>';
		$content .= '<th>'.WT_I18N::translate('Record').'</th>';
		if ($show_unassigned || $show_other) {
			$content .= '<th>'.WT_I18N::translate('Username').'</th>';
		}
		$content .= '<th>'.WT_Gedcom_Tag::getLabel('TEXT').'</th>';
		$content .= '</tr></thead><tbody>';

		$found=false;
		$end_jd=$show_future ? 99999999 : WT_CLIENT_JD;
		foreach (get_calendar_events(0, $end_jd, '_TODO', WT_GED_ID) as $todo) {
			$record=WT_GedcomRecord::getInstance($todo['id']);
			if ($record && $record->canDisplayDetails()) {
				$user_name=get_gedcom_value('_WT_USER', 2, $todo['factrec']);
				if ($user_name==WT_USER_NAME || !$user_name && $show_unassigned || $user_name && $show_other) {
					$content.='<tr>';
					//-- Event date (sortable)
					$content .= '<td>'; //hidden by datables code
					$content .= $todo['date']->JD();
					$content .= '</td>';
					$content.='<td class="wrap">'. $todo['date']->Display(empty($SEARCH_SPIDER)).'</td>';
					$name=$record->getFullName();
					$content.='<td class="wrap"><a href="'.$record->getHtmlUrl().'">'.PrintReady($name).'</a></td>';
					if ($show_unassigned || $show_other) {
						$content.='<td class="wrap">'.$user_name.'</td>';
					}
					$text=get_gedcom_value('_TODO', 1, $todo['factrec']);
					$content.='<td class="wrap">'.$text.'</td>';
					$content.='</tr>';
					$found=true;
				}
			}
		}

		$content .= '</tbody></table>';
		if (!$found) {
			$content.='<p>'.WT_I18N::translate('There are no research tasks in this family tree.').'</p>';
		}

		if ($template) {
			if ($block) {
				require WT_THEME_DIR.'templates/block_small_temp.php';
			} else {
				require WT_THEME_DIR.'templates/block_main_temp.php';
			}
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class WT_Module_Block
	public function isUserBlock() {
		return true;
	}

	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class WT_Module_Block
	public function configureBlock($block_id) {
		if (safe_POST_bool('save')) {
			set_block_setting($block_id, 'show_other',      safe_POST_bool('show_other'));
			set_block_setting($block_id, 'show_unassigned', safe_POST_bool('show_unassigned'));
			set_block_setting($block_id, 'show_future',     safe_POST_bool('show_future'));
			set_block_setting($block_id, 'block',  safe_POST_bool('block'));
			echo WT_JS_START, 'window.opener.location.href=window.opener.location.href;window.close();', WT_JS_END;
			exit;
		}

		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$show_other=get_block_setting($block_id, 'show_other', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Show research tasks that are assigned to other users');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('show_other', $show_other);
		echo '</td></tr>';

		$show_unassigned=get_block_setting($block_id, 'show_unassigned', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Show research tasks that are not assigned to any user');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('show_unassigned', $show_unassigned);
		echo '</td></tr>';

		$show_future=get_block_setting($block_id, 'show_future', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Show research tasks that have a date in the future');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('show_future', $show_future);
		echo '</td></tr>';

		$block=get_block_setting($block_id, 'block', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo /* I18N: label for a yes/no option */ WT_I18N::translate('Add a scrollbar when block contents grow');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('block', $block);
		echo '</td></tr>';
	}
}