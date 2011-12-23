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

class top10_givnnames_WT_Module extends WT_Module implements WT_Module_Block {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Top=Most common */ WT_I18N::translate('Top given names');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Top given names" module */ WT_I18N::translate('A list of the most popular given names.');
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $TEXT_DIRECTION, $ctype, $WT_IMAGES, $controller;

		$num=get_block_setting($block_id, 'num', 10);
		$infoStyle=get_block_setting($block_id, 'infoStyle', 'table');
		$block=get_block_setting($block_id, 'block', false);
		if ($cfg) {
			foreach (array('num', 'infoStyle', 'block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name=$cfg[$name];
				}
			}
		}

		$stats=new WT_Stats(WT_GEDCOM);

		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		if ($ctype=='gedcom' && WT_USER_GEDCOM_ADMIN || $ctype=='user' && WT_USER_ID) {
			$title='<img class="adminicon" src="'.$WT_IMAGES['admin'].'" width="15" height="15" alt="'.WT_I18N::translate('Configure').'"  onclick="window.open(\'index_edit.php?action=configure&amp;ctype='.$ctype.'&amp;block_id='.$block_id.'\', \'_blank\', \'top=50,left=50,width=600,height=350,scrollbars=1,resizable=1\');">';
		} else {
			$title='';
		}
		// I18N: There are separate lists of male/female names, containing %d names each
		$title .= WT_I18N::plural('Top given name', 'Top %d given names', $num, $num);

		$content = '<div class="normal_inner_block">';
		//Select List or Table
		switch ($infoStyle) {
		case "list": // Output style 1:  Simple list style.  Better suited to left side of page.
			if ($TEXT_DIRECTION=='ltr') $padding = 'padding-left: 15px';
			else $padding = 'padding-right: 15px';
			$params=array(1,$num,'rcount');
			//List Female names
			$totals=$stats->commonGivenFemaleTotals($params);
			if ($totals) {
				$content.='<b>'.WT_I18N::translate('Females').'</b><div class="wrap" style="'.$padding.'">'.$totals.'</div><br>';
			}
			//List Male names
			$totals=$stats->commonGivenMaleTotals($params);
			if ($totals) {
				$content.='<b>'.WT_I18N::translate('Males').'</b><div class="wrap" style="'.$padding.'">'.$totals.'</div><br>';
			}
			break;
		case "table": // Style 2: Tabular format.  Narrow, 2 or 3 column table, good on right side of page
			$params=array(1,$num,'rcount');
			$content.='<table style="margin:auto;">
						<tr valign="top">
						<td>'.$stats->commonGivenFemaleTable($params).'</td>
						<td>'.$stats->commonGivenMaleTable($params).'</td>';
			$content.='</tr></table>';
			break;
		}
		$content .=  "</div>";

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
		return true;
	}

	// Implement class WT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class WT_Module_Block
	public function configureBlock($block_id) {
		if (safe_POST_bool('save')) {
			set_block_setting($block_id, 'num', safe_POST_integer('num', 1, 10000, 10));
			set_block_setting($block_id, 'infoStyle', safe_POST('infoStyle', array('list', 'table'), 'table'));
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

		$infoStyle=get_block_setting($block_id, 'infoStyle', 'table');
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Presentation style');
		echo '</td><td class="optionbox">';
		echo select_edit_control('infoStyle', array('list'=>WT_I18N::translate('list'), 'table'=>WT_I18N::translate('table')), null, $infoStyle, '');
		echo '</td></tr>';

		$block=get_block_setting($block_id, 'block', false);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo /* I18N: label for a yes/no option */ WT_I18N::translate('Add a scrollbar when block contents grow');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('block', $block);
		echo '</td></tr>';
	}
}