<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Webtrees\LegacyBundle\Controller;


use Fgt\Application;
use Fgt\UrlConstants;
use Symfony\Component\HttpFoundation\Request;
use Webtrees\LegacyBundle\Legacy\AdminTreesManagePhp;

class AdminController extends AbstractController {

    public function adminTreesManagePhpAction(Request $request)
    {
        defined('WT_SCRIPT_NAME') || define('WT_SCRIPT_NAME', UrlConstants::ADMIN_TREES_MANAGE_PHP);
        $this->setConfig();
        Application::i()->init()->started();
        $class = new AdminTreesManagePhp($this->container, $request);
        $class->run();

        //require_once FGT_ROOT . DIRECTORY_SEPARATOR . UrlConstants::mapToFile(UrlConstants::INDEX_PHP);
        return $this->render(
            'WebtreesLegacyBundle:Default:output.html.twig',
            array(
                'output' => $class->getOutput(),
                'menus' => $class->getOutputMenus()
            )
        );
    }

    public function adminMediaPhpAction()
    {
    }

    public function adminMediaUploadPhpAction()
    {
    }

    public function adminModuleBlocksPhpAction()
    {
    }

    public function adminModuleMenusPhpAction()
    {
    }

    public function adminModuleReportsPhpAction()
    {
    }

    public function adminModuleSidebarPhpAction()
    {
    }

    public function adminModuleTabsPhpAction()
    {
    }

    public function adminModulesPhpAction()
    {
    }

    public function adminPgvToWtPhpAction()
    {
    }

    public function adminPhpAction()
    {
    }

    public function adminSiteAccessPhpAction()
    {
    }

    public function adminSiteChangePhpAction()
    {
    }

    public function adminSiteCleanPhpAction()
    {
    }

    public function adminSiteConfigPhpAction()
    {
    }

    public function adminSiteInfoPhpAction()
    {
    }

    public function adminSiteLogsPhpAction()
    {
    }

    public function adminSiteMergePhpAction()
    {
    }

    public function adminSiteReadmePhpAction()
    {
    }

    public function adminSiteUpgradePhpAction()
    {
    }

    public function adminTreesCheckPhpAction()
    {
    }

    public function adminTreesConfigPhpAction()
    {
    }

    public function adminTreesDownloadPhpAction()
    {
    }

    public function adminTreesExportPhpAction()
    {
    }

    public function adminTreesMergePhpAction()
    {
    }

    public function adminTreesPlacesPhpAction()
    {
    }

    public function adminTreesRenumberPhpAction()
    {
    }

    public function adminUsersBulkPhpAction()
    {
    }

    public function adminUsersPhpAction()
    {
    }
}