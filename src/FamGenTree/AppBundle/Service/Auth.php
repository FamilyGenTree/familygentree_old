<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\AppBundle\Service;

use FamGenTree\AppBundle\Entity\User;
use Fgt\Globals;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Auth extends ContainerAware
{
    const ALGORITHM_BCRYPT_NEW = 'bcrypt_10';
    const ROLE_ADMIN           = 'ROLE_ADMIN';
    const ROLE_MANAGER         = 'ROLE_MANAGER';

    public function __construct(ContainerInterface $diContainer)
    {
        $this->setContainer($diContainer);
    }

    public function login($username, $password, $salt)
    {
    }

    /**
     * @return \FamGenTree\AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }

    public function isLoggedIn()
    {
        return $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY');
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function isAdmin($user = null)
    {
        if ($user === null) {
            $user = $this->getUser();
        }

        if ($user instanceof User) {
            return $user->hasRole(static::ROLE_ADMIN);
        } else {
            return false;
        }
    }

    public function isManager($tree, $user = null)
    {
        if ($user === null) {
            $user = $this->getUser();
        }
        if ($tree === null) {
            $tree = isset(Globals::i()->WT_TREE) ? Globals::i()->WT_TREE : null;
        }

        return $this->isAdmin($user)
               || (null !== $user
                   && $tree !== null
                   && $user->hasRole(static::ROLE_MANAGER)
                   && $tree->getUserPreference($user, 'canedit') === 'admin'
        );
    }

    protected function get($string)
    {
        return $this->container->get($string);
    }
}