<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Context\Setup\Config;

class ConfigDatabase extends ConfigAbstract
{
    const DB_SYSTEM_MYSQL    = 'mysql';
    const DB_SYSTEM_POSTGRES = 'pgsql';

    protected $dbSystem           = self::DB_SYSTEM_MYSQL;
    protected $host               = 'localhost';
    protected $user               = null;
    protected $password           = null;
    protected $port               = 3306;
    protected $dbname             = null;
    protected $prefix             = 'fgt_';
    protected $confirmedMigration = false;

    /**
     * @return string
     */
    public function getDbSystem()
    {
        return $this->dbSystem;
    }

    /**
     * @param string $dbSystem
     */
    public function setDbSystem($dbSystem)
    {
        $this->dbSystem = $dbSystem;
    }


    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getDbname()
    {
        return $this->dbname;
    }

    /**
     * @param mixed $dbname
     */
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;
    }

    /**
     * @return mixed
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param mixed $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return boolean
     */
    public function isConfirmedMigration()
    {
        return $this->confirmedMigration;
    }

    /**
     * @param boolean $confirmedMigration
     */
    public function setConfirmedMigration($confirmedMigration)
    {
        $this->confirmedMigration = $confirmedMigration;
    }

    public function getDbSystemAsDbalString()
    {
        switch ($this->getDbSystem()) {
            case static::DB_SYSTEM_MYSQL:
                return 'pdo_mysql';
            case static::DB_SYSTEM_POSTGRES:
                return 'pdo_pgsql';
        }

        return null;
    }
}