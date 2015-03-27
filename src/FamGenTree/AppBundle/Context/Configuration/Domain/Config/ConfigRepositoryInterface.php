<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGenTree\AppBundle\Context\Configuration\Domain\Config;


use FamGenTree\AppBundle\Context\Configuration\Domain\FgtConfig;

interface ConfigRepositoryInterface {

    /**
     * @return FgtConfig
     */
    public function load();

    /**
     * @return FgtConfig
     */
    public function loadSetupConfig();
}