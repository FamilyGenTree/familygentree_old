<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Context\Configuration\Domain;


class ConfigValue {
    protected $value;
    protected $key;
    protected $scope;
    protected $immutable=false;

    function __construct($value, $immutable, $key, $scope)
    {
        $this->value     = $value;
        $this->immutable = $immutable;
        $this->key       = $key;
        $this->scope     = $scope;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return boolean
     */
    public function isImmutable()
    {
        return $this->immutable;
    }

    public function asBoolean() {
        return (bool)$this->value;
    }

    public function __toString()
    {
        return $this->value;
    }


}