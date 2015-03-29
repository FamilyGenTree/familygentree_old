<?php
namespace Webtrees\LegacyBundle\Legacy;

/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use Fgt\Application;
use Fgt\Globals;
use Fgt\UrlConstants;
use Webtrees\LegacyBundle\Context\Application\Controller\AbstractSymfonyConnectorController;
use Webtrees\LegacyBundle\Context\Application\View\LegacyPageController;

defined('WT_SCRIPT_NAME') || define('WT_SCRIPT_NAME', UrlConstants::INDEX_PHP);

class IndexPHP extends AbstractSymfonyConnectorController
{

    public function run()
    {
        $this->getRequest()->get('action');
// The only option for action is "ajax"
        $action = Filter::get('action');
        switch ($action) {
            case 'ajax':
                // We generate individual blocks using AJAX
                $this->actionAjax();
                break;
            default:
                $this->actionHtml();
                break;
        }
        echo $this->viewModel->render();
    }

    /**
     */
    protected function actionAjax()
    {
        $this->setViewModel(new AjaxController($this->getTemplating()));

        list($ctype, $blocks, $all_blocks) = $this->commonAction();

        $this->viewModel->pageHeader();

        // Check we’re displaying an allowable block.
        $block_id = Filter::getInteger('block_id');
        if (array_key_exists($block_id, $blocks['main'])) {
            $module_name = $blocks['main'][$block_id];
        } elseif (array_key_exists($block_id, $blocks['side'])) {
            $module_name = $blocks['side'][$block_id];
        } else {

            return;
        }
        if (array_key_exists($module_name, $all_blocks)) {
            $class_name = __NAMESPACE__ . '\\' . $module_name . '_WT_Module';
            $module     = new $class_name();
            $this->viewModel[] = $module->getBlock($block_id);
        }
        if (Database::i()->isDebugSql()) {
            $this->viewModel[] = Database::i()->getQueryLog();
        }
        return;
    }

    protected function actionHtml()
    {
        $this->setViewModel(new PageController($this->getTemplating()));

        list($ctype, $blocks, $all_blocks) = $this->commonAction();

        if ($ctype === 'user') {
            $this->viewModel->restrictAccess(Auth::check());
        }
        $this->viewModel
            ->setPageTitle($ctype === 'user' ? I18N::translate('My page') : WT_TREE_TITLE)
            ->setMetaRobots('index,follow')
            ->setCanonicalUrl(UrlConstants::url(WT_SCRIPT_NAME, array(
                'ctype' => $ctype,
                'ged'   => WT_GEDCOM
            )))
            ->pageHeader()
            // By default jQuery modifies AJAX URLs to disable caching, causing JS libraries to be loaded many times.
            ->addInlineJavascript('jQuery.ajaxSetup({cache:true});');
        if ($ctype === 'user') {
            $this->viewModel[] = '<div id="my-page">';
            $this->viewModel[] = '<h1 class="center">' . I18N::translate('My page') . '</h1>';
        } else {
            $this->viewModel[] = '<div id="home-page">';
        }
        if ($blocks['main']) {
            $this->renderMainBlocks($blocks, $ctype);
        }
        if ($blocks['side']) {
            $this->renderSideBlocks($blocks, $ctype);
        }

        $this->viewModel[] = '<div id="link_change_blocks">';

        if ($ctype === 'user') {
            $this->viewModel[] = '<a href="index_edit.php?user_id=' . Auth::id() . '">' . I18N::translate('Change the blocks on this page') . '</a>';
        } elseif ($ctype === 'gedcom' && WT_USER_GEDCOM_ADMIN) {
            $this->viewModel[] = '<a href="index_edit.php?gedcom_id=' . WT_GED_ID . '">' . I18N::translate('Change the blocks on this page') . '</a>';
        }

        if (Globals::i()->WT_TREE->getPreference('SHOW_COUNTER')) {
            $this->viewModel[] = '<span>' . I18N::translate('Hit count:') . ' ' . Globals::i()->hitCount . '</span>';
        }

        $this->viewModel[] = '</div></div>';
    }

    /**
     * @param $blocks
     * @param $ctype
     *
     * @return array
     */
    protected function renderMainBlocks($blocks, $ctype)
    {
        if ($blocks['side']) {
            $this->viewModel[] = '<div id="index_main_blocks">';
        } else {
            $this->viewModel[] = '<div id="index_full_blocks">';
        }
        foreach ($blocks['main'] as $block_id => $module_name) {
            $class_name = __NAMESPACE__ . '\\' . $module_name . '_WT_Module';
            $module     = new $class_name;
            if (Globals::i()->SEARCH_SPIDER || !$module->loadAjax()) {
                // Load the block directly
                $this->viewModel[] = $module->getBlock($block_id);
            } else {
                // Load the block asynchronously
                $this->viewModel[] = '<div id="block_' . $block_id . '"><div class="loading-image">&nbsp;</div></div>';
                $this->viewModel->addInlineJavascript(
                    'jQuery("#block_' . $block_id . '").load("'
                    . UrlConstants::url(UrlConstants::INDEX_PHP, array(
                        'ctype'    => $ctype,
                        'action'   => 'ajax',
                        'block_id' => $block_id
                    )) . '");'
                );
            }
        }
        $this->viewModel[] = '</div>';
    }

    /**
     * @param $blocks
     * @param $ctype
     */
    protected function renderSideBlocks($blocks, $ctype)
    {
        if ($blocks['main']) {
            $this->viewModel[] = '<div id="index_small_blocks">';
        } else {
            $this->viewModel[] = '<div id="index_full_blocks">';
        }
        foreach ($blocks['side'] as $block_id => $module_name) {
            $class_name = __NAMESPACE__ . '\\' . $module_name . '_WT_Module';
            $module     = new $class_name;
            if (Globals::i()->SEARCH_SPIDER || !$module->loadAjax()) {
                // Load the block directly
                $this->viewModel[] = $module->getBlock($block_id);
            } else {
                // Load the block asynchronously
                $this->viewModel[] = '<div id="block_' . $block_id . '"><div class="loading-image">&nbsp;</div></div>';
                $this->viewModel->addInlineJavascript(
                    'jQuery("#block_' . $block_id . '").load("'
                    . UrlConstants::url(UrlConstants::INDEX_PHP, array(
                        'ctype'    => $ctype,
                        'action'   => 'ajax',
                        'block_id' => $block_id
                    )) . '");'
                );
            }
        }
        $this->viewModel[] = '</div>';
    }

    /**
     * @return array
     */
    protected function commonAction()
    {
// The default view depends on whether we are logged in
        if (Auth::check()) {
            $ctype = Filter::get('ctype', 'gedcom|user', 'user');
        } else {
            $ctype = 'gedcom';
        }

// Get the blocks list
        if ($ctype === 'user') {
            $blocks = FunctionsDbPhp::i()->get_user_blocks(Auth::id());
        } else {
            $blocks = FunctionsDbPhp::i()->get_gedcom_blocks(WT_GED_ID);
        }

        $all_blocks = Module::getActiveBlocks();

// The latest version is shown on the administration page.  This updates it every day.
        Functions::i()->fetch_latest_version();

        return array(
            $ctype,
            $blocks,
            $all_blocks
        );
    }


}