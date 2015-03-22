<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\AppBundle\Context\Configuration\Domain\SymfonyParameters;

class ParametersDatabase
{

    const DB_SYSTEM_MYSQL      = 'mysql';
    const DB_SYSTEM_POSTGRESQL = 'pgsql';
    protected $dbSystem;
    protected $host;
    protected $user;
    protected $password;
    protected $port;
    protected $dbname;
    protected $prefix;

    public function __construct($dbSystem = self::DB_SYSTEM_MYSQL, $dbname = null, $user = null, $password = null, $prefix = 'fgt_', $host = 'localhost', $port = null)
    {
        $this->dbSystem = $dbSystem;
        $this->host     = $host;
        $this->user     = $user;
        $this->password = $password;
        $this->port     = $port;
        $this->dbname   = $dbname;
        $this->prefix   = $prefix;
    }


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
}