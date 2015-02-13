<?php

namespace Webtrees\LegacyThemeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WebtreesLegacyThemeBundle:Default:index.html.twig', array('name' => $name));
    }
}
