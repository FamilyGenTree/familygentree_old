<?php

namespace Webtrees\LegacyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WebtreesLegacyBundle:Default:index.html.twig', array('name' => $name));
    }
}
