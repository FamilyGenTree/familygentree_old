<?php

namespace Webtrees\LegacyBundle\Controller;

use Fgt\Config;
use Fgt\UrlConstants;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{

    public function indexAction(Request $request, $file)
    {
        try {
            $this->setConfig();
            require_once FGT_ROOT . '/' . $file;
        } catch (\Exception $e) {
            echo "$e";
        }

        return $this->render('WebtreesLegacyBundle:Default:index.html.twig', array('name' => $file));
    }

    public function indexPhpAction()
    {
        $this->setConfig();

        require_once FGT_ROOT . DIRECTORY_SEPARATOR . UrlConstants::mapToFile(UrlConstants::INDEX_PHP);
        return $this->render('WebtreesLegacyBundle:Default:index.html.twig', array('name' => 'index'));
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

    public function loginPhpAction()
    {
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
