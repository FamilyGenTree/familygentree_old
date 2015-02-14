<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Fgt;


class Config
{
    /**
     * data.media.directory
     * data.media.directory.thumbs
     */
    const DATA_MEDIA_DIRECTORY            = 'data.media.directory';
    const DATA_MEDIA_DIRECTORY_THUMBNAILS = 'data.media.directory.thumbs';
    const DATA_DIRECTORY                  = 'directory.data';
    const CONFIG_PATH                     = 'config.file.name';
    const BASE_URL                        = 'url.base';
    const CACHE                           = 'cache.service';
    const CACHE_DIR                       = 'directory.cache';
    const MODULES_DIR                     = 'directory.modules';

    /**
     * @var Config
     */
    protected static $instance;
    /**
     * @var array
     */
    protected $valueStore = array();

    /**
     * Singleton protected
     */
    protected function __construct()
    {

    }

    /**
     * @return Config
     */
    public static function i()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public static function get($name, $default = null)
    {
        return static::i()->__isset($name) ? static::i()->__get($name) : $default;
    }

    public static function set($name, $value)
    {
        static::i()->__set($name, $value);
    }

    public function __get($name)
    {
        if (!array_key_exists($name, $this->valueStore)) {
            throw new \Exception("$name was never initialized, but accessed. Unknown result.");
        }

        return $this->__isset($name) ? $this->valueStore[$name] : null;
    }

    public function __set($name, $value)
    {
        $this->valueStore[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->valueStore[$name]);
    }

    public function __unset($name)
    {
        unset($this->valueStore[$name]);
    }
}