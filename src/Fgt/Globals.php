<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Fgt;


use \Exception;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;

/**
 * Class Globals
 *
 * @property \Zend_Session                 WT_SESSION
 * @property Tree                          WT_TREE
 * @property string|mixed                  TEXT_DIRECTION
 * @property \Zend_Controller_Request_Http WT_REQUEST
 * @property string                        SEARCH_SPIDER
 * @property string                        GEDCOM
 * @property string                        DATE_FORMAT
 *
 * @package Fgt
 *
 * @author  Christoph Graupner <ch.graupner@workingdeveloper.de>
 * @since   ${DATE}
 */
class Globals
{
    /**
     * @var array
     */
    protected static $ALLOWED_VARS = array(
        'WT_SESSION'     => 1,
        'WT_TREE'        => 1,
        'TEXT_DIRECTION' => 1,
        'WT_REQUEST'     => 1,
        'SEARCH_SPIDER'  => 1,
        'GEDCOM'         => 1,
        'DATE_FORMAT'    => 'initDateFormat'
    );
    protected static $instance;
    protected        $varContainer = array();

    /**
     * Singleton protected
     */
    protected function __construct()
    {
    }


    /**
     * @return Globals
     */
    public static function i()
    {
        if (null === static::$instance) {
            static::$instance = new self();
        }

        return static::$instance;
    }


    /**
     * @param $name
     *
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        if (!isset(static::$ALLOWED_VARS[$name])) {
            throw new Exception("$name is not an allowed global");
        }
        if (isset($this->varContainer[$name])) {
            return $this->varContainer[$name];
        } else {
            if (method_exists($this, static::$ALLOWED_VARS[$name])) {
                $this->varContainer[$name] = $this->{static::$ALLOWED_VARS[$name]};

                return $this->varContainer[$name];
            }
        }
        throw new Exception("$name was never initialized, but accessed. Unknown result.");
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws Exception
     */
    public function __set($name, $value)
    {
        if (!isset(static::$ALLOWED_VARS[$name])) {
            throw new Exception("$name is not an allowed global");
        }
        $this->varContainer[$name] = $value;
    }

    /**
     * @param $name
     *
     * @return bool
     * @throws Exception
     */
    public function __isset($name)
    {
        if (!isset(static::$ALLOWED_VARS[$name])) {
            throw new Exception("$name is not an allowed global");
        }

        return isset($this->varContainer[$name]);
    }

    /**
     * @param $name
     *
     * @throws Exception
     */
    public function __unset($name)
    {
        if (!isset(static::$ALLOWED_VARS[$name])) {
            throw new Exception("$name is not an allowed global");
        }
        unset($this->varContainer[$name]);
    }

    /**
     *
     */
    protected function initDateFormat()
    {
        // I18N: This is the format string for full dates.  See http://php.net/date for codes
        return I18N::noop('%j %F %Y');
    }

}