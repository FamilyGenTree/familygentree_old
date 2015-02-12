<?php

namespace FamGeneTree\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('FamGeneTreeAppBundle:Default:index.html.twig', array('name' => $name));
    }
}
