<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Webtrees\LegacyBundle\Context\Application\Service;


use Webtrees\LegacyBundle\Legacy\Auth;
use Webtrees\LegacyBundle\Legacy\FunctionsDbPhp;

class Blocks {
    public function getUserBlocks($user_id) {
        return FunctionsDbPhp::i()->get_user_blocks($user_id);
    }

    public function getGedcomBlocks($gedcom_id) {
        return FunctionsDbPhp::i()->get_gedcom_blocks($gedcom_id);
    }
}