<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Fgt;


use Fisharebest\Webtrees\I18N;

class Constants
{
    const UNKNOWN_PN = 'Unknown given name';
    const UNKNOWN_NN = 'Unknown surname';

    public static $STANDARD_NAME_FACTS = array(
        'NAME',
        'NPFX',
        'GIVN',
        'SPFX',
        'SURN',
        'NSFX'
    );

    public static function UNKNOWN_PN()
    {
        return I18N::translate_c(Constants::UNKNOWN_PN, '…');
    }

    public static function UNKNOWN_NN()
    {
        return I18N::translate_c(Constants::UNKNOWN_NN, '…');
    }

}