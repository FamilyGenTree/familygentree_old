<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Entity;


use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser implements AdvancedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var String
     *
     * @ORM\Column(name="password_algorithm",type="string",length=15,options={"default" = "bcrypt_10"})
     */
    protected $passwordAlgorithm = null;

    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->passwordAlgorithm =  \FamGeneTree\AppBundle\Service\Auth::ALGORITHM_BCRYPT_NEW;
    }

}