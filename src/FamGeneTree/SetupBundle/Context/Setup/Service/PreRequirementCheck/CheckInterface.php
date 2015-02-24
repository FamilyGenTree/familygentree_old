<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck;

use FamGeneTree\SetupBundle\Context\Setup\ValueObject\PreRequirementResult;

/**
 * Interface CheckInterface
 *
 * @package FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck
 */
interface CheckInterface
{
    /**
     * @return null
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