<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\AttributeOverrides({
 *      @ORM\AttributeOverride(name="username",
 *          column=@ORM\Column(
 *              name     = "user_name",
 *              nullable = false,
 *              unique   = true,
 *              length   = 100
 *          )
 *      ),
 * })
 */
class User extends BaseUser implements AdvancedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="user_id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", name="real_name",nullable=true,length=100)
     */
    protected $realName;

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
        $this->passwordAlgorithm = \FamGenTree\AppBundle\Service\Auth::ALGORITHM_BCRYPT_NEW;
    }
}