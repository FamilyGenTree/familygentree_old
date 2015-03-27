<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck;

use FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\ValueObject\PreRequirementResult;

/**
 * Interface CheckInterface
 *
 * @package FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck
 */
interface CheckInterface
{
    /**
     */
    public function run();

    /**
     * @return PreRequirementResult[]
     */
    public function getResults();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();

    public function isPassed();
}