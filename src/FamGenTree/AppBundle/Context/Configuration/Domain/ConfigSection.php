<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\AppBundle\Context\Configuration\Domain;


use FamGenTree\AppBundle\Context\Configuration\Domain\Exception\ImmutableValue;

abstract class ConfigSection
{

    protected $store = array();
    protected $sectionScope = null;

    /**
     * @param string   $key
     * @param mixed    $value
     * @param bool     $immutable
     * @param int|null $scope
     *
     * @throws \FamGenTree\AppBundle\Context\Configuration\Domain\Exception\ImmutableValue
     */
    public function setValue($key, $value, $immutable = false, $scope = null)
    {
        if (isset($this->sectionScope) && $this->getValue($key)->isImmutable()) {
            throw new ImmutableValue("Value for $key is set already and cannot be modified. Existing value: {$this->getValue($key)}");
        }
        $this->store[$key] = new ConfigValue(
            $value,
            $immutable,
            $key,
            $scope === null ? $this->sectionScope : $scope
        );
    }

    /**
     * @param $key
     *
     * @return ConfigValue
     */
    public function getValue($key)
    {
        return $this->store[$key];
    }

    public function isDefined($key)
    {
        return array_key_exists($key, $this->store);
    }

    public function __get($name)
    {
        // TODO: Implement __get() method.
    }

    public function __isset($name)
    {
        // TODO: Implement __isset() method.
    }

    public function __set($name, $value)
    {
        // TODO: Implement __set() method.
    }

    public function __unset($name)
    {
        // TODO: Implement __unset() method.
    }


}