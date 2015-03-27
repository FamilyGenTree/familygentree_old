<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Context\Setup\Step;

class StepResultAggregate extends StepResult
{
    protected $results = array();

    public function __construct($name)
    {
        parent::__construct($name, StepResult::STATE_SUCCESS);
    }

    public function addResult(StepResult $result)
    {
        $this->results[] = $result;
        $this->state     = static::maxState($this->state, $result->getState());
    }

    public function getResults()
    {
        return $this->results;
    }
}