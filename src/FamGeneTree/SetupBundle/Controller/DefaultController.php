<?php

namespace FamGeneTree\SetupBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('FamGeneTreeSetupBundle:Default:index.html.twig', array('name' => 'Welcome'));
    }
}
