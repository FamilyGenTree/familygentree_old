<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FamGenTree\AppBundle\Service\Auth;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Webtrees\LegacyBundle\Legacy\LegacyUserInterface;

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
class User
    extends BaseUser
    implements AdvancedUserInterface, LegacyUserInterface
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

    public function __construct($userData = null)
    {
        parent::__construct();
        // your own logic
        $this->passwordAlgorithm = Auth::ALGORITHM_BCRYPT_NEW;
        if (is_object($userData)) {
            foreach (array(
                         'user_id'   => 'id',
                         'user_name' => 'userName',
                         'real_name' => 'realName',
                         'email'     => 'email'
                     ) as $var => $field) {
                $this->{$field} = $userData->{$var};
            }
        }
    }

    /**
     * Delete a user
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /** Validate a supplied password
     *
     * @param string $password
     *
     * @return boolean
     */
    public function checkPassword($password)
    {
        // TODO: Implement checkPassword() method.
    }

    /**
     * Get the numeric ID for this user.
     *
     * @return string
     *
     * @deprecated use getId()
     */
    public function getUserId()
    {
        return $this->getId();
    }

    /**
     * Get the real name of this user.
     *
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * Set the real name of this user.
     *
     * @param string $realName
     *
     * @return \Webtrees\LegacyBundle\Legacy\User
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;
    }

    /**
     * Fetch a user option/setting from the wt_user_setting table.
     *
     * Since we'll fetch several settings for each user, and since there aren’t
     * that many of them, fetch them all in one database query
     *
     * @param string      $setting_name
     * @param string|null $default
     *
     * @return string|null
     */
    public function getPreference($setting_name, $default = null)
    {
        // TODO: Implement getPreference() method.
    }

    /**
     * Update a setting for the user.
     *
     * @param string $setting_name
     * @param string $setting_value
     *
     * @return \Webtrees\LegacyBundle\Legacy\User
     */
    public function setPreference($setting_name, $setting_value)
    {
        // TODO: Implement setPreference() method.
    }

    /**
     * Delete a setting for the user.
     *
     * @param string $setting_name
     *
     * @return \Webtrees\LegacyBundle\Legacy\User
     */
    public function deletePreference($setting_name)
    {
        // TODO: Implement deletePreference() method.
    }
}