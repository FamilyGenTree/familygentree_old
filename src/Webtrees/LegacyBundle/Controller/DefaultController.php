<?php

namespace Webtrees\LegacyBundle\Controller;

use Fgt\Application;
use Fgt\UrlConstants;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Webtrees\LegacyBundle\Legacy\IndexPHP;
use Webtrees\LegacyBundle\Legacy\LoginPhp;

class DefaultController extends AbstractController
{

    public function themesAssetAction($themeName, $file)
    {
        $explode = explode('.', $file);

        $contentType = null;
        switch (strtolower(array_reverse($explode)[0])) {
            case 'css':
                $contentType = 'text/css';
                break;
            case 'js':
                $contentType = 'application/javascript';
                break;
            case 'png':
                $contentType = 'image/png';
                break;
            case 'jpg':
                $contentType = 'image/jpeg';
                break;
            case 'gif':
                $contentType = 'image/gif';
                break;

            default:
                $contentType = 'text/plain';
        }

        return new BinaryFileResponse(
            $this->getLegacyRoot() . "/themes/{$themeName}/{$file}",
            BinaryFileResponse::HTTP_OK,
            array(
                'Content-Type' => $contentType
            )
        );
    }

//    public function indexAction(Request $request, $file)
//    {
//        try {
//            $this->setConfig();
//            require_once FGT_ROOT . '/' . $file;
//        } catch (\Exception $e) {
//            echo "$e";
//        }
//
//        return $this->render('WebtreesLegacyBundle:Default:index.html.twig', array('name' => $file));
//    }

    public function indexPhpAction(Request $request)
    {
        defined('WT_SCRIPT_NAME') || define('WT_SCRIPT_NAME', UrlConstants::INDEX_PHP);
        $this->setConfig();
        Application::i()->init()->started();
        $class = new IndexPHP($this->container, $request);
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

    public function indexEditPhpAction()
    {
        $this->setConfig();

        require_once FGT_ROOT . DIRECTORY_SEPARATOR . UrlConstants::mapToFile(UrlConstants::INDEX_EDIT_PHP);
    }

    public function indilistPhpAction()
    {
    }

    public function individualPhpAction()
    {
    }

    public function inverselinkPhpAction()
    {
    }

    public function lifespanPhpAction()
    {
    }

    public function loginPhpAction(Request $request)
    {
        $this->setConfig();
        require_once FGT_ROOT . DIRECTORY_SEPARATOR . UrlConstants::mapToFile(UrlConstants::LOGIN_PHP);
        $class = new LoginPhp($this->container, $request);
        $class->run();

        //require_once FGT_ROOT . DIRECTORY_SEPARATOR . UrlConstants::mapToFile(UrlConstants::INDEX_PHP);
        return $this->render(
            'WebtreesLegacyBundle:Default:output.html.twig',
            array(
                'output' => $class->getOutput(),
                'menus' => $class->getOutputMenus()
            )
        );
//        $class = new IndexPHP($this->container, $request);
//        $class->run();
//
//        //require_once FGT_ROOT . DIRECTORY_SEPARATOR . UrlConstants::mapToFile(UrlConstants::INDEX_PHP);
//        return $this->render(
//            'WebtreesLegacyBundle:Default:output.html.twig',
//            array(
//                'output' => $class->getOutput(),
//                'menus' => $class->getOutputMenus()
//            )
//        );
        return $this->render(
            'WebtreesLegacyBundle:Default:index.html.twig',array());
    }

    public function logoutPhpAction()
    {
    }

    public function mediafirewallPhpAction()
    {
    }

    public function medialistPhpAction()
    {
    }

    public function mediaviewerPhpAction()
    {
    }

    public function messagePhpAction()
    {
    }

    public function modulePhpAction()
    {
    }

    public function notePhpAction()
    {
    }

    public function notelistPhpAction()
    {
    }

    public function pedigreePhpAction()
    {
    }

    public function placelistPhpAction()
    {
    }

    public function relationshipPhpAction()
    {
    }

    public function repoPhpAction()
    {
    }

    public function repolistPhpAction()
    {
    }

    public function reportenginePhpAction()
    {
    }

    public function searchAdvancedPhpAction()
    {
    }

    public function searchPhpAction()
    {
    }

    public function sourcePhpAction()
    {
    }

    public function sourcelistPhpAction()
    {
    }

    public function statisticsPhpAction()
    {
    }

    public function statisticsplotPhpAction()
    {
    }

    public function timelinePhpAction()
    {
    }

    public function ancestryPhpAction()
    {
    }

    public function autocompletePhpAction()
    {
    }

    public function blockEditPhpAction()
    {
    }

    public function branchesPhpAction()
    {
    }

    public function calendarPhpAction()
    {
    }

    public function compactPhpAction()
    {
    }

    public function descendancyPhpAction()
    {
    }

    public function editChangesPhpAction()
    {
    }

    public function editInterfacePhpAction()
    {
    }

    public function editNewsPhpAction()
    {
    }

    public function editUserPhpAction()
    {
    }

    public function expandViewPhpAction()
    {
    }

    public function familyBookPhpAction()
    {
    }

    public function familyPhpAction()
    {
    }

    public function famlistPhpAction()
    {
    }

    public function fanchartPhpAction()
    {
    }

    public function findPhpAction()
    {
    }

    public function gedrecordPhpAction()
    {
    }

    public function helpTextPhpAction()
    {
    }

    public function hourglassAjaxPhpAction()
    {
    }

    public function hourglassPhpAction()
    {
    }

    public function importPhpAction()
    {
    }
}
