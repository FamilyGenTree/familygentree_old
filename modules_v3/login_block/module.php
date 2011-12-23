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

class login_block_WT_Module extends WT_Module implements WT_Module_Block {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Login');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Login" module */ WT_I18N::translate('An alternative way to login and logout.');
	}

	// Implement class WT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		if (WT_USER_ID) {
			$title = WT_I18N::translate('Logout');


			$content = '<div class="center"><form method="post" action="index.php?logout=1" name="logoutform" onsubmit="return true;">';
			$content .= '<br><a href="edituser.php" class="name2">'.WT_I18N::translate('Logged in as ').' ('.WT_USER_NAME.')</a><br><br>';

			$content .= "<input type=\"submit\" value=\"".WT_I18N::translate('Logout')."\">";

			$content .= "<br><br></form></div>";
		} else {
			$title = WT_I18N::translate('Login');
			$LOGIN_URL=get_site_setting('LOGIN_URL');
			$content = "<div class=\"center\"><form method=\"post\" action=\"$LOGIN_URL\" name=\"loginform\" onsubmit=\"t = new Date(); document.loginform.usertime.value=t.getFullYear()+'-'+(t.getMonth()+1)+'-'+t.getDate()+' '+t.getHours()+':'+t.getMinutes()+':'+t.getSeconds(); return true;\">";
			$content .= "<input type=\"hidden\" name=\"url\" value=\"index.php\">";
			$content .= "<input type=\"hidden\" name=\"ged\" value=\"";
			$content .= WT_GEDCOM;
			$content .= "\">";
			$content .= "<input type=\"hidden\" name=\"pid\" value=\"";
			if (isset($pid)) $content .= $pid;
			$content .= "\">";
			$content .= "<input type=\"hidden\" name=\"usertime\" value=\"\">";
			$content .= "<input type=\"hidden\" name=\"action\" value=\"login\">";
			$content .= "<table class=\"center\">";

			// Row 1: Userid
			$content .= "<tr><td>";
			$content .= WT_I18N::translate('Username');
			$content .= help_link('username');
			$content .= "</td><td><input type=\"text\" name=\"username\"  size=\"20\" class=\"formField\">";
			$content .= "</td></tr>";

			// Row 2: Password
			$content .= "<tr><td>";
			$content .= WT_I18N::translate('Password');
			$content .= help_link('password');
			$content .= "</td><td ";
			$content .= "><input type=\"password\" name=\"password\"  size=\"20\" class=\"formField\">";
			$content .= "</td></tr>";

			// Row 3: "Login" link
			$content .= "<tr><td colspan=\"2\" class=\"center\">";
			$content .= "<input type=\"submit\" value=\"".WT_I18N::translate('Login')."\">&nbsp;";
			$content .= "</td></tr>";
			$content .= "</table><table class=\"center\">";

			if (get_site_setting('USE_REGISTRATION_MODULE')) {

				// Row 4: "Request Account" link
				$content .= "<tr><td><br>";
				$content .= WT_I18N::translate('No account?');
				$content .= help_link('new_user');
				$content .= "</td><td><br>";
				$content .= "<a href=\"login_register.php?action=register\">";
				$content .= WT_I18N::translate('Request new user account');
				$content .= "</a>";
				$content .= "</td></tr>";

				// Row 5: "Lost Password" link
				$content .= "<tr><td>";
				$content .= WT_I18N::translate('Lost your password?');
				$content .= help_link('new_password');
				$content .= "</td><td>";
				$content .= "<a href=\"login_register.php?action=pwlost\">";
				$content .= WT_I18N::translate('Request new password');
				$content .= "</a>";
				$content .= "</td></tr>";
			}

			$content .= "</table>";
			$content .= "</form></div>";
		}

		if ($template) {
			require WT_THEME_DIR.'templates/block_main_temp.php';
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
	}
}