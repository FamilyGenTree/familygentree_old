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

/**
 * Class User - Provide an interface to the wt_user table.
 */
class User
{
    /** @var  string The primary key of this user. */
    private $user_id;

    /** @var array Cached copy of the wt_user_setting table. */
    private $preferences;

    /** @var  User[] Only fetch users from the database once. */
    private static $cache = array();

    /**
     * Find the user with a specified user_id.
     *
     * @param integer|null $user_id
     *
     * @return User|null
     */
    public static function find($user_id)
    {
        if (!array_key_exists($user_id, self::$cache)) {
            $row = Database::i()->prepare(
                "SELECT SQL_CACHE user_id, user_name, real_name, email FROM `##user` WHERE user_id = ?"
            )
                           ->execute(array($user_id))
                           ->fetchOneRow();
            if ($row) {
                self::$cache[$user_id] = new User($row);
            } else {
                self::$cache[$user_id] = null;
            }
        }

        return self::$cache[$user_id];
    }

    /**
     * Find the user with a specified user_id.
     *
     * @param string $identifier
     *
     * @return User|null
     */
    public static function findByIdentifier($identifier)
    {
        $user_id = Database::i()->prepare(
            "SELECT SQL_CACHE user_id FROM `##user` WHERE ? IN (user_name, email)"
        )
                           ->execute(array($identifier))
                           ->fetchOne();

        return self::find($user_id);
    }

    /**
     * Find the user with a specified genealogy record.
     *
     * @param Tree       $tree
     * @param Individual $individual
     *
     * @return User|null
     */
    public static function findByGenealogyRecord(Tree $tree, Individual $individual)
    {
        $user_id = Database::i()->prepare(
            "SELECT SQL_CACHE user_id" .
            " FROM `##user_gedcom_setting`" .
            " WHERE gedcom_id = ? AND setting_name = 'gedcomid' AND setting_value = ?"
        )
                           ->execute(array(
                                         $tree->getTreeId(),
                                         $individual->getXref()
                                     ))
                           ->fetchOne();

        return self::find($user_id);
    }

    /**
     * Find the latest user to register.
     *
     * @return User|null
     */
    public static function findLatestToRegister()
    {
        $user_id = Database::i()->prepare(
            "SELECT SQL_CACHE u.user_id" .
            " FROM `##user` u" .
            " LEFT JOIN `##user_setting` us ON (u.user_id=us.user_id AND us.setting_name='reg_timestamp') " .
            " ORDER BY us.setting_value DESC LIMIT 1"
        )
                           ->execute()
                           ->fetchOne();

        return self::find($user_id);
    }

    /**
     * Create a new user.
     *
     * The calling code needs to check for duplicates identifiers before calling
     * this function.
     *
     * @param string $user_name
     * @param string $real_name
     * @param string $email
     * @param string $password
     *
     * @return User
     */
    public static function create($user_name, $real_name, $email, $password)
    {
        Database::i()->prepare(
            "INSERT INTO `##user` (user_name, real_name, email, password) VALUES (:user_name, :real_name, :email, :password)"
        )
                ->execute(array(
                              'user_name' => $user_name,
                              'real_name' => $real_name,
                              'email'     => $email,
                              'password'  => password_hash($password, PASSWORD_DEFAULT),
                          ));

        // Set default blocks for this user
        $user = User::findByIdentifier($user_name);
        Database::i()->prepare(
            "INSERT INTO `##block` (`user_id`, `location`, `block_order`, `module_name`)" .
            " SELECT :user_id , `location`, `block_order`, `module_name` FROM `##block` WHERE `user_id` = -1"
        )
                ->execute(array('user_id' => $user->getUserId()));

        return $user;
    }

    /**
     * Get a count of all users.
     *
     * @return integer
     */
    public static function count()
    {
        return (int)Database::i()->prepare(
            "SELECT SQL_CACHE COUNT(*)" .
            " FROM `##user`" .
            " WHERE user_id > 0"
        )
                            ->fetchOne();
    }

    /**
     * Get a list of all users.
     *
     * @return User[]
     */
    public static function all()
    {
        $users = array();

        $rows = Database::i()->prepare(
            "SELECT SQL_CACHE user_id, user_name, real_name, email" .
            " FROM `##user`" .
            " WHERE user_id > 0" .
            " ORDER BY user_name"
        )
                        ->fetchAll();

        foreach ($rows as $row) {
            $users[] = new User($row);
        }

        return $users;
    }

    /**
     * Get a list of all administrators.
     *
     * @return User[]
     */
    public static function allAdmins()
    {
        $rows = Database::i()->prepare(
            "SELECT SQL_CACHE user_id, user_name, real_name, email" .
            " FROM `##user`" .
            " JOIN `##user_setting` USING (user_id)" .
            " WHERE user_id > 0" .
            "   AND setting_name = 'canadmin'" .
            "   AND setting_value = '1'"
        )
                        ->fetchAll();

        $users = array();
        foreach ($rows as $row) {
            $users[] = new User($row);
        }

        return $users;
    }

    /**
     * Get a list of all verified uses.
     *
     * @return User[]
     */
    public static function allVerified()
    {
        $rows = Database::i()->prepare(
            "SELECT SQL_CACHE user_id, user_name, real_name, email" .
            " FROM `##user`" .
            " JOIN `##user_setting` USING (user_id)" .
            " WHERE user_id > 0" .
            "   AND setting_name = 'verified'" .
            "   AND setting_value = '1'"
        )
                        ->fetchAll();

        $users = array();
        foreach ($rows as $row) {
            $users[] = new User($row);
        }

        return $users;
    }

    /**
     * Get a list of all users who are currently logged in.
     *
     * @return User[]
     */
    public static function allLoggedIn()
    {
        $rows = Database::i()->prepare(
            "SELECT SQL_NO_CACHE DISTINCT user_id, user_name, real_name, email" .
            " FROM `##user`" .
            " JOIN `##session` USING (user_id)"
        )
                        ->fetchAll();

        $users = array();
        foreach ($rows as $row) {
            $users[] = new User($row);
        }

        return $users;
    }

    /**
     * Delete a user
     */
    public function delete()
    {
        // Don't delete the logs.
        Database::i()->prepare("UPDATE `##log` SET user_id=NULL WHERE user_id =?")
                ->execute(array($this->user_id));
        // Take over the user’s pending changes. (What else could we do with them?)
        Database::i()->prepare("DELETE FROM `##change` WHERE user_id=? AND status='accepted'")
                ->execute(array($this->user_id));
        Database::i()->prepare("UPDATE `##change` SET user_id=? WHERE user_id=?")
                ->execute(array(
                              $this->user_id,
                              $this->user_id
                          ));
        Database::i()->prepare("DELETE `##block_setting` FROM `##block_setting` JOIN `##block` USING (block_id) WHERE user_id=?")
                ->execute(array($this->user_id));
        Database::i()->prepare("DELETE FROM `##block` WHERE user_id=?")
                ->execute(array($this->user_id));
        Database::i()->prepare("DELETE FROM `##user_gedcom_setting` WHERE user_id=?")
                ->execute(array($this->user_id));
        Database::i()->prepare("DELETE FROM `##gedcom_setting` WHERE setting_value=? AND setting_name in ('CONTACT_USER_ID', 'WEBMASTER_USER_ID')")
                ->execute(array($this->user_id));
        Database::i()->prepare("DELETE FROM `##user_setting` WHERE user_id=?")
                ->execute(array($this->user_id));
        Database::i()->prepare("DELETE FROM `##message` WHERE user_id=?")
                ->execute(array($this->user_id));
        Database::i()->prepare("DELETE FROM `##user` WHERE user_id=?")
                ->execute(array($this->user_id));
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
        if ($this->preferences === null) {
            if ($this->user_id) {
                $this->preferences = Database::i()->prepare(
                    "SELECT SQL_CACHE setting_name, setting_value FROM `##user_setting` WHERE user_id = ?"
                )
                                             ->execute(array($this->user_id))
                                             ->fetchAssoc();
            } else {
                // Not logged in?  We have no preferences.
                $this->preferences = array();
            }
        }

        if (array_key_exists($setting_name, $this->preferences)) {
            return $this->preferences[$setting_name];
        } else {
            return $default;
        }
    }

    /**
     * Update a setting for the user.
     *
     * @param string $setting_name
     * @param string $setting_value
     *
     * @return User
     */
    public function setPreference($setting_name, $setting_value)
    {
        if ($this->user_id && $this->getPreference($setting_name) !== $setting_value) {
            Database::i()->prepare("REPLACE INTO `##user_setting` (user_id, setting_name, setting_value) VALUES (?, ?, LEFT(?, 255))")
                    ->execute(array(
                                  $this->user_id,
                                  $setting_name,
                                  $setting_value
                              ));
            $this->preferences[$setting_name] = $setting_value;
        }

        return $this;
    }

    /**
     * Delete a setting for the user.
     *
     * @param string $setting_name
     *
     * @return User
     */
    public function deletePreference($setting_name)
    {
        if ($this->user_id && $this->getPreference($setting_name) !== null) {
            Database::i()->prepare("DELETE FROM `##user_setting` WHERE user_id = ? AND setting_name = ?")
                    ->execute(array(
                                  $this->user_id,
                                  $setting_name
                              ));
            unset($this->preferences[$setting_name]);
        }

        return $this;
    }
}
