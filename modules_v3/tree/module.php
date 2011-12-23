<?php
// TreeView module class
//
// Tip : you could change the number of generations loaded before ajax calls both in individual page and in treeview page to optimize speed and server load 
//
// Copyright (C) 2011 webtrees development team
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

class tree_WT_Module extends WT_Module implements WT_Module_Tab {	
	var $headers; // CSS and script to include in the top of <head> section, before theme's CSS
	var $js; // the TreeViewHandler javascript
	
	function __construct() {
		// define the module inclusions for the page header
		$this->css=WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/css/treeview.css';
  	$this->js =WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/js/treeview.js';
	}
	
	// Extend WT_Module. This title should be normalized when this module will be added officially
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Interactive tree');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Interactive tree" module */ WT_I18N::translate('An interactive tree, showing all the ancestors and descendants of a person.');
	}
	
	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 68;
	}

	// Implement WT_Module_Tab
	public function getJSCallback() {
		return '';
}

	// Implement WT_Module_Tab
	public function getTabContent() {
		global $controller;

		require_once WT_MODULES_DIR.$this->getName().'/class_treeview.php';
    $tv = new TreeView('tvTab');
    list($html, $js) = $tv->drawViewport($controller->record->getXref(), 3);
		return
			'<script type="text/javascript" src="'.$this->js.'"></script>'.
			$html.
			WT_JS_START.'
			if (document.createStyleSheet) {
				document.createStyleSheet("'.$this->css.'"); // For Internet Explorer
			} else {
				jQuery("head").append(\'<link rel="stylesheet" type="text/css" href="'.$this->css.'">\');
			}'.
			$js.
			WT_JS_END;
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		global $SEARCH_SPIDER;
			
		return !$SEARCH_SPIDER;
	}
	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return false;
	}
	// Implement WT_Module_Tab
	public function canLoadAjax() {
		return true;
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
	}

  // Extend WT_Module
  // We define here actions to proceed when called, either by Ajax or not
  public function modAction($mod_action) {  
		require_once WT_MODULES_DIR.$this->getName().'/class_treeview.php';
    switch($mod_action) {
      case 'treeview':
				global $controller;
				$controller=new WT_Controller_Base();

        $tvName = 'tv';
        $rootid = safe_GET('rootid');
        $tv = new TreeView('tv');
				ob_start();
				$person=WT_Person::getInstance($rootid);

				if (!$person) {
					$person=$controller->getSignificantIndividual();
				}

				list($html, $js)=$tv->drawViewport($rootid, 4);

				$controller
					->setPageTitle(WT_I18N::translate('Interactive tree of %s', $person->getFullName()))
					->pageHeader()
					->addExternalJavaScript($this->js)
					->addInlineJavaScript($js)
					->addInlineJavaScript('
					if (document.createStyleSheet) {
						document.createStyleSheet("'.$this->css.'"); // For Internet Explorer
					} else {
						jQuery("head").append(\'<link rel="stylesheet" type="text/css" href="'.$this->css.'">\');
					}
				');

        if (WT_USE_LIGHTBOX) {
        	require WT_MODULES_DIR.'lightbox/functions/lb_call_js.php';
				}

				echo $html;
        break;

      case 'getDetails':
				header('Content-Type: text/html; charset=UTF-8');
        $pid = safe_GET('pid');
        $i = safe_GET('instance');
        $tv = new TreeView($i);
        echo $tv->getDetails($pid);
        break;

      case 'getPersons':
        $q = $_REQUEST["q"];
        $i = safe_GET('instance');
        $tv = new TreeView($i);
        echo $tv->getPersons($q);
        break;

			// dynamically load full medias instead of thumbnails for opened boxes before printing
      case 'getMedias':
        $q = $_REQUEST["q"];
        $i = safe_GET('instance');
        $tv = new TreeView($i);
        echo $tv->getMedias($q);
      	break;

      default:
				header('HTTP/1.0 404 Not Found');
    }
  }
}