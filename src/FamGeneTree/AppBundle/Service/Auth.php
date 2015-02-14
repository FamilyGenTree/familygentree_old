<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Service;


use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Auth implements ContainerAwareInterface
{
    const ALGORITHM_BCRYPT_NEW = 'bcrypt_12';

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
    }

    public function login($username, $password, $salt) {

    }

    /**
     * @return \FamGeneTree\AppBundle\Entity\User
     */
    public function getUser() {
        return $this->get('security.token_storage')->getToken()->getUser();
    }

    public function isLoggedIn()
    {
        return $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY');
    }


    protected function get($string)
    {
        return $this->container->get($string);
    }


}