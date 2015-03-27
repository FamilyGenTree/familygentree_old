<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Tests\Context\Step;

use FamGenTree\SetupBundle\Context\Setup\Step\StepResult;

class StepResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $stateWinner
     * @param $stateLooser
     *
     * @dataProvider dataProviderTestMaxState
     */
    public function testMaxState($stateWinner, $stateLooser)
    {
        $this->assertEquals(
            $stateWinner,
            StepResult::maxState(
                $stateWinner,
                $stateLooser
            )
        );
        $this->assertEquals(
            $stateWinner,
            StepResult::maxState(
                $stateLooser,
                $stateWinner
            )
        );
    }

    public function dataProviderTestMaxState()
    {
        return array(
            array(
                StepResult::STATE_FAILED,
                StepResult::STATE_FAILED
            ),
            array(
                StepResult::STATE_FAILED,
                StepResult::STATE_OK
            ),
            array(
                StepResult::STATE_FAILED,
                StepResult::STATE_WARNING
            ),
            array(
                StepResult::STATE_FAILED,
                StepResult::STATE_SUCCESS
            ),
            array(
                StepResult::STATE_WARNING,
                StepResult::STATE_WARNING
            ),
            array(
                StepResult::STATE_WARNING,
                StepResult::STATE_OK
            ),
            array(
                StepResult::STATE_WARNING,
                StepResult::STATE_SUCCESS
            ),
            array(
                StepResult::STATE_OK,
                StepResult::STATE_OK
            ),
            array(
                StepResult::STATE_SUCCESS,
                StepResult::STATE_SUCCESS
            ),
        );
    }
}
