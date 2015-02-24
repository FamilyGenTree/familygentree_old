<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\ValueObject;

/**
 * Class PreRequirementResult
 *
 * @package FamGeneTree\SetupBundle\Context\Setup\ValueObject
 * @author  Christoph Graupner <ch.graupner@workingdeveloper.de>
 */
class PreRequirementResult
{
    const STATE_SUCCESS = 'success';
    const STATE_FAILED  = 'failed';
    const STATE_WARNING = 'warning';
    const STATE_OK      = 'OK';

    protected $state;
    protected $name;
    protected $message;

    function __construct($name, $state, $message = null)
    {
        $this->state   = $state;
        $this->name    = $name;
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    public function isSuccess()
    {
        return $this->state === static::STATE_SUCCESS || $this->state === static::STATE_OK;
    }

    public function isWarning()
    {
        return $this->state === static::STATE_WARNING;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getMessage()
    {
        return $this->message;
    }
}