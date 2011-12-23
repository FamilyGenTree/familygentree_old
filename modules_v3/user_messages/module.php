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

class user_messages_WT_Module extends WT_Module implements WT_Module_Block {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Messages');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Messages" module */ WT_I18N::translate('Communicate directly with other users, using private messages.');
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $ctype, $TEXT_DIRECTION, $WT_IMAGES;

		require_once WT_ROOT.'includes/functions/functions_print_facts.php';

		// Block actions
		$action=safe_GET('action');
		$message_id=safe_GET('message_id');
		if ($action=='deletemessage') {
			if (is_array($message_id)) {
				foreach ($message_id as $msg_id) {
					deleteMessage($msg_id);
				}
			} else {
				deleteMessage($message_id);
			}
		}
		$block=get_block_setting($block_id, 'block', true);
		if ($cfg) {
			foreach (array('block') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name=$cfg[$name];
				}
			}
		}
		$usermessages = getUserMessages(WT_USER_ID);

		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		$title=WT_I18N::plural('%s message', '%s messages',count($usermessages), count($usermessages));
		$content = '';
		$content .= "<form name=\"messageform\" action=\"index.php?ctype={$ctype}\" method=\"get\" onsubmit=\"return confirm('".WT_I18N::translate('Are you sure you want to delete this message?  It cannot be retrieved later.')."');\">";
		if (get_user_count()>1) {
			$content .= '<br>'.WT_I18N::translate('Send Message')." <select name=\"touser\">";
			$content .= '<option value="">' . WT_I18N::translate('&lt;select&gt;') . '</option>';
			foreach (get_all_users() as $user_id=>$user_name) {
				if ($user_id!=WT_USER_ID && get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'contactmethod')!='none') {
					$content .= "<option value=\"".$user_name."\">".PrintReady(getUserFullName($user_id)).' ';
					if ($TEXT_DIRECTION=='ltr') {
						$content .= stripLRMRLM(getLRM().' - '.$user_name.getLRM());
					} else {
						$content .= stripLRMRLM(getRLM().' - '.$user_name.getRLM());
					}
					$content .= '</option>';
				}
			}
			$content .= "</select> <input type=\"button\" value=\"".WT_I18N::translate('Send')."\" onclick=\"message(document.messageform.touser.options[document.messageform.touser.selectedIndex].value, 'messaging2', ''); return false;\"><br><br>";
		}
		if (count($usermessages)==0) {
			$content .= WT_I18N::translate('You have no pending messages.')."<br>";
		} else {
			$content .= WT_JS_START.'function select_all() {';
			foreach ($usermessages as $message) {
				$content .= 'var cb=document.getElementById("cb_message'.$message['id'].'");';
				$content .= 'cb.checked=!cb.checked;';
			}
			$content .= 'return false;}'.WT_JS_END;
			$content .= '<input type="hidden" name="action" value="deletemessage">';
			$content .= '<table class="list_table"><tr>';
			$content .= '<td class="list_label">'.WT_I18N::translate('Delete')."<br><a href=\"#\" onclick=\"return select_all();\">".WT_I18N::translate('All').'</a></td>';
			$content .= '<td class="list_label">'.WT_I18N::translate('Subject:').'</td>';
			$content .= '<td class="list_label">'.WT_I18N::translate('Date Sent:').'</td>';
			$content .= '<td class="list_label">'.WT_I18N::translate('Email Address:').'</td>';
			$content .= '</tr>';
			foreach ($usermessages as $key=>$message) {
				if (isset($message['id'])) $key = $message['id'];
				$content .= '<tr>';
				$content .= "<td class=\"list_value_wrap\"><input type=\"checkbox\" id=\"cb_message$key\" name=\"message_id[]\" value=\"$key\"></td>";
				$showmsg=preg_replace("/(\w)\/(\w)/","\$1/<span style=\"font-size:1px;\"> </span>\$2",PrintReady($message['subject']));
				$showmsg=str_replace("@","@<span style=\"font-size:1px;\"> </span>",$showmsg);
				$content .= "<td class=\"list_value_wrap\"><a href=\"#\" onclick=\"expand_layer('message{$key}'); return false;\"><img id=\"message{$key}_img\" src=\"".$WT_IMAGES['plus']."\" alt=\"".WT_I18N::translate('Show Details')."\" title=\"".WT_I18N::translate('Show Details')."\"> <b>".$showmsg."</b></a></td>";
				$content .= '<td class="list_value_wrap">'.format_timestamp($message['created']).'</td>';
				$content .= '<td class="list_value_wrap">';
				$user_id=get_user_id($message['from']);
				if ($user_id) {
					$content .= PrintReady(getUserFullName($user_id));
					if ($TEXT_DIRECTION=='ltr') {
						$content .= ' '.getLRM().' - '.htmlspecialchars(getUserEmail($user_id)) . getLRM();
					} else {
						$content .= ' '.getRLM().' - '.htmlspecialchars(getUserEmail($user_id)) . getRLM();
					}
				} else {
					$content .= "<a href=\"mailto:".$message['from']."\">".str_replace("@","@<span style=\"font-size:1px;\"> </span>",$message['from']).'</a>';
				}
				$content .= '</td>';
				$content .= '</tr>';
				$content .= "<tr><td class=\"list_value_wrap\" colspan=\"5\"><div id=\"message$key\" style=\"display: none;\">";
				$message['body'] = nl2br(htmlspecialchars($message['body']));
				$message['body'] = expand_urls($message['body']);

				$content .= PrintReady($message['body']).'<br><br>';
				if (strpos($message['subject'], /* I18N: When replying to an email, the subject becomes "RE: <subject>" */ WT_I18N::translate('RE: '))!==0) {
					$message['subject']= WT_I18N::translate('RE: ').$message['subject'];
				}
				if ($user_id) {
					$content .= "<a href=\"#\" onclick=\"reply('".addslashes($message['from'])."', '".addslashes($message['subject'])."'); return false;\">".WT_I18N::translate('Reply').'</a> | ';
				}
				$content .= "<a href=\"index.php?action=deletemessage&amp;message_id={$key}\" onclick=\"return confirm('".WT_I18N::translate('Are you sure you want to delete this message?  It cannot be retrieved later.')."');\">".WT_I18N::translate('Delete').'</a></div></td></tr>';
			}
			$content .= '</table>';
			$content .= '<input type="submit" value="'.WT_I18N::translate('Delete Selected Messages').'"><br>';
		}
		$content .= '</form>';

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
		return false;
	}

	// Implement class WT_Module_Block
	public function configureBlock($block_id) {
		if (safe_POST_bool('save')) {
			set_block_setting($block_id, 'block',  safe_POST_bool('block'));
			echo WT_JS_START, 'window.opener.location.href=window.opener.location.href;window.close();', WT_JS_END;
			exit;
		}

		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$block=get_block_setting($block_id, 'block', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo /* I18N: label for a yes/no option */ WT_I18N::translate('Add a scrollbar when block contents grow');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('block', $block);
		echo '</td></tr>';
	}
}