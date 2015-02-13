<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Webtrees\LegacyBundle\Context\Application\Service;


use FamGeneTree\AppBundle\Context\Configuration\Domain\FgtConfig;
use Webtrees\LegacyBundle\Legacy\BaseTheme;

class Theme {
    function __construct(FgtConfig $config)
    {
    }


    /**
     * @return string[]
     */
    public function getAvailableThemes() {
        return ['webtrees'];
    }

    /**
     * @return BaseTheme
     */
    public function getTheme() {
        return \Webtrees\LegacyBundle\Legacy\Theme::theme();
    }
}