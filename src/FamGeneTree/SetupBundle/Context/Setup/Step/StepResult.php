<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Step;

class StepResult
{
    const STATE_SUCCESS = 'success';
    const STATE_FAILED  = 'failed';
    const STATE_WARNING = 'warning';
    const STATE_OK      = 'OK';

    protected $state;
    protected $name;
    protected $message;

    public function __construct($name, $state, $message = null)
    {
        $this->state = $state;
        $this->name = $name;
        $this->message = $message;
    }

    public static function maxState($state1, $state2)
    {
        switch ($state1) {
            case static::STATE_FAILED:
                return $state1;
            case static::STATE_WARNING:
                return $state2 === static::STATE_FAILED ? $state2 : $state1;
            case static::STATE_OK:
            case static::STATE_SUCCESS:
            default:
                return in_array($state2, array(
                    static::STATE_FAILED,
                    static::STATE_WARNING
                ))
                    ? $state2
                    : $state1;
        }
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