<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Service;


use FamGeneTree\AppBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Auth extends ContainerAware
{
    const ALGORITHM_BCRYPT_NEW = 'bcrypt_12';

    public function __construct(ContainerInterface $diContainer)
    {
        $this->setContainer($diContainer);
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

    /**
     * @param User|\Webtrees\LegacyBundle\Legacy\User $user
     */
    public function isAdmin($user)
    {
        if ($user instanceof \Webtrees\LegacyBundle\Legacy\User) {

        } elseif ($user instanceof User) {

        } else {

        }
    }


}