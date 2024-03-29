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

use PDO;
use PDOException;

/**
 * Class Database Class - Extend PHP's native PDO class
 * to provide database access with logging, etc.
 */
class Database
{
    /** @var Database Implement the singleton pattern */
    private static $instance;

    /** @var PDO Native PHP database driver */
    private $pdo;

    /** @var array Keep a log of all the SQL statements that we execute */
    private $log;

    private $prefix;

    private $debugSql = false;

    public static function i()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }

    /**
     * Prevent instantiation via new Database
     */
    private final function __construct()
    {
        $this->log = array();
    }

    /**
     * Prevent instantiation via clone()
     *
     * @throws \Exception
     */
    public final function __clone()
    {
        throw new \Exception('Database::i()->clone() is not allowed.');
    }

    /**
     * Prevent instantiation via serialize()
     *
     * @throws \Exception
     */
    public final function __wakeup()
    {
        throw new \Exception('Database::i()->unserialize() is not allowed.');
    }

    /**
     * Begin a transaction.
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commit this transaction.
     *
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Disconnect from the server, so we can connect to another one
     *
     * @return void
     */
    public function disconnect()
    {
        $this->pdo = null;
    }

    /**
     * Implement the singleton pattern, using a static accessor.
     *
     * @param string $DBHOST
     * @param string $DBPORT
     * @param string $DBNAME
     * @param string $DBUSER
     * @param string $DBPASS
     *
     * @throws \Exception
     */
    public function createInstance($DBHOST, $DBPORT, $DBNAME, $DBUSER, $DBPASS, $dbPrefix)
    {
        if ($this->pdo instanceof PDO) {
            throw new \Exception('Database::i()->createInstance() can only be called once.');
        }
        $this->prefix = $dbPrefix;
        // Create the underlying PDO object
        $this->pdo = new PDO(
            (substr($DBHOST, 0, 1) === '/'
                ?
                "mysql:unix_socket={$DBHOST};dbname={$DBNAME}"
                :
                "mysql:host={$DBHOST};dbname={$DBNAME};port={$DBPORT}"
            ),
            $DBUSER, $DBPASS,
            array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_CASE               => PDO::CASE_LOWER,
                PDO::ATTR_AUTOCOMMIT         => true
            )
        );
        $this->pdo->exec("SET NAMES UTF8");
    }

    /**
     * We don't access $instance directly, only via query(), exec() and prepare()
     *
     * @return Database
     *
     * @throws \Exception
     */
    public function getInstance()
    {
        if ($this->pdo instanceof PDO) {
            return self::$instance;
        } else {
            throw new \Exception('createInstance() must be called before getInstance().');
        }
    }

    /**
     * Are we currently connected to a database?
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->pdo instanceof PDO;
    }

    /**
     * Log the details of a query, for debugging and analysis.
     *
     * @param string   $query
     * @param integer  $rows
     * @param double   $microtime
     * @param string[] $bind_variables
     *
     * @return void
     */
    public function logQuery($query, $rows, $microtime, $bind_variables)
    {
        if ($this->isDebugSql()) {
            // Full logging
            // Trace
            $trace = debug_backtrace();
            array_shift($trace);
            array_shift($trace);
            foreach ($trace as $n => $frame) {
                if (isset($frame['file']) && isset($frame['line'])) {
                    $trace[$n] = basename($frame['file']) . ':' . $frame['line'] . ' ' . $frame['function'];
                } else {
                    unset($trace[$n]);
                }
            }
            $stack = '<abbr title="' . Filter::escapeHtml(implode(" / ", $trace)) . '">' . (count($this->log) + 1) . '</abbr>';
            // Bind variables
            $query2 = '';
            foreach ($bind_variables as $key => $value) {
                if (is_null($value)) {
                    $bind_variables[$key] = '[NULL]';
                }
            }
            foreach (str_split(Filter::escapeHtml($query)) as $char) {
                if ($char == '?') {
                    $query2 .= '<abbr title="' . Filter::escapeHtml(array_shift($bind_variables)) . '">' . $char . '</abbr>';
                } else {
                    $query2 .= $char;
                }
            }
            // Highlight embedded literal strings.
            if (preg_match('/[\'"]/', $query)) {
                $query2 = '<span style="background-color:yellow;">' . $query2 . '</span>';
            }
            // Highlight slow queries
            $microtime *= 1000; // convert to milliseconds
            if ($microtime > 1000) {
                $microtime = sprintf('<span style="background-color: #ff0000;">%.3f</span>', $microtime);
            } elseif ($microtime > 100) {
                $microtime = sprintf('<span style="background-color: #ffa500;">%.3f</span>', $microtime);
            } elseif ($microtime > 1) {
                $microtime = sprintf('<span style="background-color: #ffff00;">%.3f</span>', $microtime);
            } else {
                $microtime = sprintf('%.3f', $microtime);
            }
            $this->log[] = "<tr><td>{$stack}</td><td>{$query2}</td><td>{$rows}</td><td>{$microtime}</td></tr>";
        } else {
            // Just log query count for statistics
            $this->log[] = true;
        }
    }

    /**
     * Determine the number of queries executed, for the page statistics.
     *
     * @return integer
     */
    public function getQueryCount()
    {
        return count($this->log);
    }

    /**
     * Convert the query log into an HTML table.
     *
     * @return string
     */
    public function getQueryLog()
    {
        $html      = '<table border="1"><col span="3"><col align="char"><thead><tr><th>#</th><th>Query</th><th>Rows</th><th>Time (ms)</th></tr></thead><tbody>' . implode('', $this->log) . '</tbody></table>';
        $this->log = array();

        return $html;
    }

    /**
     * Determine the most recently created value of an AUTO_INCREMENT field.
     *
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Quote a string for embedding in a MySQL statement.
     *
     * The native quote() function does not convert PHP nulls to DB nulls
     *
     * @param  $string
     *
     * @return string
     *
     * @deprecated We should use bind-variables instead.
     */
    public function quote($string)
    {
        if (is_null($string)) {
            return 'NULL';
        } else {
            return $this->pdo->quote($string, PDO::PARAM_STR);
        }
    }

    /**
     * Execute an SQL statement, and log the result.
     *
     * @param string $sql The SQL statement to execute
     *
     * @return integer The number of rows affected by this SQL query
     */
    public function exec($sql)
    {
        $sql   = str_replace('##', $this->prefix, $sql);
        $start = microtime(true);
        $rows  = $this->pdo->exec($sql);
        $end   = microtime(true);
        self::logQuery($sql, $rows, $end - $start, array());

        return $rows;
    }

    /**
     * Prepare an SQL statement for execution.
     *
     * @param $sql
     *
     * @return Statement
     * @throws \Exception
     */
    public function prepare($sql)
    {
        if (!$this->pdo instanceof PDO) {
            throw new \Exception("No Connection Established");
        }
        $sql = str_replace('##', $this->prefix, $sql);

        return new Statement($this->pdo->prepare($sql));
    }

    /**
     * Roll back this transaction.
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * Run a series of scripts to bring the database schema up to date.
     *
     * @param string  $schema_dir
     * @param string  $schema_name
     * @param integer $target_version
     *
     * @return void
     * @throws \Exception
     */
    public function updateSchema($schema_dir, $schema_name, $target_version)
    {
        try {
            $current_version = (int)Site::getPreference($schema_name);
        } catch (PDOException $e) {
            // During initial installation, this table won’t exist.
            // It will only be a problem if we can’t subsequently create it.
            $current_version = 0;
        }

        // During installation, the current version is set to a special value of
        // -1 (v1.2.5 to v1.2.7) or -2 (v1.3.0 onwards).  This indicates that the tables have
        // been created, and we are already at the latest version.
        switch ($current_version) {
            case -1:
                // Due to a bug in webtrees 1.2.5 - 1.2.7, the setup value of "-1"
                // wasn't being updated.
                $current_version = 12;
                Site::setPreference($schema_name, $current_version);
                break;
            case -2:
                // Because of the above bug, we now set the version to -2 during setup.
                $current_version = $target_version;
                Site::setPreference($schema_name, $current_version);
                break;
        }

        // Update the schema, one version at a time.
        while ($current_version < $target_version) {
            $next_version = $current_version + 1;
            require $schema_dir . 'db_schema_' . $current_version . '_' . $next_version . '.php';
            // The updatescript should update the version or throw an exception
            $current_version = (int)Site::getPreference($schema_name);
            if ($current_version != $next_version) {
                throw new \Exception("Internal error while updating {$schema_name} to {$next_version}");
            }
        }
    }

    /**
     * @return boolean
     */
    public function isDebugSql()
    {
        return $this->debugSql;
    }

    /**
     * @param boolean $debugSql
     */
    public function setDebugSql($debugSql)
    {
        $this->debugSql = $debugSql;
    }

}
