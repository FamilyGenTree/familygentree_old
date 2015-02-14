<?php

namespace Webtrees\LegacyThemeBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class WebtreesLegacyThemeBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
