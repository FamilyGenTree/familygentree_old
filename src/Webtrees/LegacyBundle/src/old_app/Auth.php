<?php
namespace Webtrees\LegacyBundle\Legacy;

/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use Fgt\Application;
use Fgt\Globals;
use Zend_Session;

/**
 * Class Auth - authentication functions
 */
class Auth
{
    protected static $authService = null;

    /**
     * Are we currently logged in?
     *
     * @return boolean
     */
    public static function check()
    {
        return self::getAuthService()->isLoggedIn();
    }

    /**
     * Is the specified/current user an administrator?
     *
     * @param null|\Webtrees\LegacyBundle\Legacy\LegacyUserInterface|\Webtrees\LegacyBundle\Legacy\User $user
     *
     * @return bool
     */
    public static function isAdmin(LegacyUserInterface $user = null)
    {
        return self::getAuthService()->isAdmin($user);
    }

    /**
     * Is a user a manager of a tree?
     *
     * @param Tree|null $tree
     * @param User|null $user
     *
     * @return boolean
     */
    public static function isManager(Tree $tree = null, User $user = null)
    {
        if ($tree === null) {
            $tree = isset(Globals::i()->WT_TREE) ? Globals::i()->WT_TREE : null;
        }

        if ($user === null) {
            $user = self::user();
        }

        return self::isAdmin($user) || ($user && $tree && $tree->getUserPreference($user, 'canedit') === 'admin');
    }

    /**
     * Is a user a moderator of a tree?
     *
     * @param Tree|null $tree
     * @param User|null $user
     *
     * @return boolean
     */
    public static function isModerator(Tree $tree = null, User $user = null)
    {
        if ($tree === null) {
            $tree = Globals::i()->WT_TREE;
        }

        if ($user === null) {
            $user = self::user();
        }

        return self::isManager($tree, $user) || $user && $tree->getUserPreference($user, 'canedit') === 'accept';
    }

    /**
     * Is a user an editor of a tree?
     *
     * @param Tree|null $tree
     * @param User|null $user
     *
     *
     * @return boolean
     */
    public static function isEditor(Tree $tree = null, User $user = null)
    {
        if ($tree === null) {
            $tree = Globals::i()->WT_TREE;
        }

        if ($user === null) {
            $user = self::user();
        }

        return self::isModerator($tree, $user) || $user && $tree->getUserPreference($user, 'canedit') === 'edit';
    }

    /**
     * Is a user a member of a tree?
     *
     * @param Tree|null $tree
     * @param User|null $user
     *
     * @return boolean
     */
    public static function isMember(Tree $tree = null, User $user = null)
    {
        if ($tree === null) {
            $tree = Globals::i()->WT_TREE;
        }

        if ($user === null) {
            $user = self::user();
        }

        return self::isEditor($tree, $user) || $user && $tree->getUserPreference($user, 'canedit') === 'access';
    }

    /**
     * The ID of the authenticated user, from the current session.
     *
     * @return string|null
     */
    public static function id()
    {

        return Application::i()->getSession()->wt_user;
    }

    /**
     * The authenticated user, from the current session.
     *
     * @return User
     */
    public static function user()
    {
        return Application::i()->getAuthService()->getUser();
        //$user = User::find(Auth::id());
        //if ($user === null) {
        //    $visitor            = new \stdClass;
        //    $visitor->user_id   = '';
        //    $visitor->user_name = '';
        //    $visitor->real_name = '';
        //    $visitor->email     = '';
        //
        //    return new User($visitor);
        //} else {
        //    return $user;
        //}
    }

    /**
     * Login directly as an explicit user - for masquerading.
     *
     * @param User $user
     */
    public static function login(User $user)
    {

        Application::i()->getSession()->wt_user = $user->getUserId();
        Zend_Session::regenerateId();
    }

    /**
     * End the session for the current user.
     */
    public static function logout()
    {
        Zend_Session::regenerateId();
        Zend_Session::destroy();
    }

    /**
     * @return \FamGenTree\AppBundle\Service\Auth
     */
    protected static function getAuthService()
    {
        if (null === self::$authService) {
            self::$authService = Application::i()->getAuthService();
        }
        return self::$authService;
    }

}
