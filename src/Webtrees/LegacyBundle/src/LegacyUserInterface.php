<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */
namespace Webtrees\LegacyBundle\Legacy;

/**
 * Class User - Provide an interface to the wt_user table.
 */
interface LegacyUserInterface
{
    /**
     * Delete a user
     */
    public function delete();

    /** Validate a supplied password
     *
     * @param string $password
     *
     * @return boolean
     */
    public function checkPassword($password);

    /**
     * Get the numeric ID for this user.
     *
     * @return string
     */
    public function getUserId();

    /**
     * Get the login name for this user.
     *
     * @return string
     */
    public function getUserName();

    /**
     * Set the login name for this user.
     *
     * @param string $user_name
     *
     * @return $this
     */
    public function setUserName($user_name);

    /**
     * Get the real name of this user.
     *
     * @return string
     */
    public function getRealName();

    /**
     * Set the real name of this user.
     *
     * @param string $realName
     *
     * @return User
     */
    public function setRealName($realName);

    /**
     * Get the email address of this user.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set the email address of this user.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email);

    /**
     * Set the password of this user.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password);

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
    public function getPreference($setting_name, $default = null);

    /**
     * Update a setting for the user.
     *
     * @param string $setting_name
     * @param string $setting_value
     *
     * @return User
     */
    public function setPreference($setting_name, $setting_value);

    /**
     * Delete a setting for the user.
     *
     * @param string $setting_name
     *
     * @return User
     */
    public function deletePreference($setting_name);
}