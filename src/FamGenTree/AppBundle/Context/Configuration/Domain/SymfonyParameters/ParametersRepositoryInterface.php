<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGenTree\AppBundle\Context\Configuration\Domain\SymfonyParameters;

interface ParametersRepositoryInterface
{
    /**
     * @return SymfonyParameters
     */
    public function loadParametersTemplate();

    /**
     * @return SymfonyParameters
     */
    public function loadParameters();

    public function writeParameters(SymfonyParameters $symfonyParameters);
}