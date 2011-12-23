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

class top10_pageviews_WT_Module extends WT_Module implements WT_Module_Block {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Most viewed pages');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Most visited pages" module */ WT_I18N::translate('A list of the pages that have been viewed the most number of times.');
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $ctype, $WT_IMAGES, $SHOW_COUNTER;

		$count_placement=get_block_setting($block_id, 'count_placement', 'before');
		$num=(int)get_block_setting($block_id, 'num', 10);
		$block=get_block_setting($block_id, 'block', false);
		if ($cfg) {
			foreach (array('count_placement', 'num', 'block') as $name) {
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
		$title.=$this->getTitle();

		$content = "";
		// load the lines from the file
		$top10=WT_DB::prepare(
			"SELECT page_parameter, page_count".
			" FROM `##hit_counter`".
			" WHERE gedcom_id=? AND page_name IN ('individual.php','family.php','source.php','repo.php','note.php','mediaviewer.php')".
			" ORDER BY page_count DESC LIMIT ".$num
		)->execute(array(WT_GED_ID))->FetchAssoc();


		if ($block) {
			$content .= "<table width=\"90%\">";
		} else {
			$content .= "<table>";
		}
		foreach ($top10 as $id=>$count) {
			$record=WT_GedcomRecord::getInstance($id);
			if ($record && $record->canDisplayDetails()) {
				$content .= '<tr valign="top">';
				if ($count_placement=='before') {
					$content .= '<td dir="ltr" align="right">['.$count.']</td>';
				}
				$content .= '<td class="name2" ><a href="'.$record->getHtmlUrl().'">'.$record->getFullName().'</a></td>';
				if ($count_placement=='after') {
					$content .= '<td dir="ltr" align="right">['.$count.']</td>';
				}
				$content .= '</tr>';
			}
		}
		$content .= "</table>";

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
		return true;
	}

	// Implement class WT_Module_Block
	public function isUserBlock() {
		return false;
	}

	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class WT_Module_Block
	public function configureBlock($block_id) {
		if (safe_POST_bool('save')) {
			set_block_setting($block_id, 'num',  safe_POST_integer('num', 1, 10000));
			set_block_setting($block_id, 'count_placement',  safe_POST('count_placement', array('before', 'after'), 'before'));
			set_block_setting($block_id, 'block',  safe_POST_bool('block'));
			echo WT_JS_START, 'window.opener.location.href=window.opener.location.href;window.close();', WT_JS_END;
			exit;
		}
		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$num=get_block_setting($block_id, 'num', 10);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Number of items to show');
		echo '</td><td class="optionbox">';
		echo '<input type="text" name="num" size="2" value="', $num, '">';
		echo '</td></tr>';

		$count_placement=get_block_setting($block_id, 'count_placement', 'left');
		echo "<tr><td class=\"descriptionbox wrap width33\">";
		echo WT_I18N::translate('Place counts before or after name?');
		echo "</td><td class=\"optionbox\">";
		echo select_edit_control('count_placement', array('before'=>WT_I18N::translate('before'), 'after'=>WT_I18N::translate('after')), null, $count_placement, '');
		echo '</td></tr>';

		$block=get_block_setting($block_id, 'block', false);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo /* I18N: label for a yes/no option */ WT_I18N::translate('Add a scrollbar when block contents grow');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('block', $block);
		echo '</td></tr>';
	}
}