<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Webtrees\LegacyBundle\Controller;


use Fgt\Config;
use Symfony\Component\HttpFoundation\Request;

class SetupController extends AbstractController {
    public function indexAction(Request $request)
    {
        $this->setConfig();
//        echo $file;

        require_once FGT_ROOT . '/setup.php';
        var_dump($request);
        die;

        return $this->render('WebtreesLegacyBundle:Default:index.html.twig', array('name' => $file));
    }
}