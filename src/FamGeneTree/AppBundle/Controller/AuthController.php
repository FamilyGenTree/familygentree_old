<?php

namespace FamGeneTree\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AuthController extends Controller
{
    public function loginAction()
    {
        return $this->render('FamGeneTreeAppBundle:Auth:login.html.twig', array());
    }

    public function logoutAction()
    {
    }
}
