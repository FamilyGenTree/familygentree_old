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

    /**
     * @return string
     */
    public static function UNKNOWN_PN()
    {
        return I18N::translate_c(Constants::UNKNOWN_PN, '…');
    }

    /**
     * @return string
     */
    public static function UNKNOWN_NN()
    {
        return I18N::translate_c(Constants::UNKNOWN_NN, '…');
    }

    /**
     * @var array
     */
    public static $STANDARD_NAME_FACTS = array(
        'NAME',
        'NPFX',
        'GIVN',
        'SPFX',
        'SURN',
        'NSFX'
    );

    /**
     * NPFX tags - name prefixes
     *
     * @var array
     */
    public static $NPFX_ACCEPT = array(
        'Adm',
        'Amb',
        'Brig',
        'Can',
        'Capt',
        'Chan',
        'Chapln',
        'Cmdr',
        'Col',
        'Cpl',
        'Cpt',
        'Dr',
        'Gen',
        'Gov',
        'Hon',
        'Lady',
        'Lt',
        'Mr',
        'Mrs',
        'Ms',
        'Msgr',
        'Pfc',
        'Pres',
        'Prof',
        'Pvt',
        'Rabbi',
        'Rep',
        'Rev',
        'Sen',
        'Sgt',
        'Sir',
        'Sr',
        'Sra',
        'Srta',
        'Ven',
    );

    /**
     * FILE:FORM tags - file formats
     *
     * @var array
     */
    public static $FILE_FORM_ACCEPT = array(
        'avi',
        'bmp',
        'gif',
        'jpeg',
        'mp3',
        'ole',
        'pcx',
        'png',
        'tiff',
        'wav',
    );

    /**
     * Fact tags (as opposed to event tags), that don't normally have a value
     *
     * @var array
     */
    public static $EMPTY_FACTS = array(
        'ADOP',
        'ANUL',
        'BAPL',
        'BAPM',
        'BARM',
        'BASM',
        'BIRT',
        'BLES',
        'BURI',
        'CENS',
        'CHAN',
        'CHR',
        'CHRA',
        'CONF',
        'CONL',
        'CREM',
        'DATA',
        'DEAT',
        'DIV',
        'DIVF',
        'EMIG',
        'ENDL',
        'ENGA',
        'FCOM',
        'GRAD',
        'HUSB',
        'IMMI',
        'MAP',
        'MARB',
        'MARC',
        'MARL',
        'MARR',
        'MARS',
        'NATU',
        'ORDN',
        'PROB',
        'RESI',
        'RETI',
        'SLGC',
        'SLGS',
        'WIFE',
        'WILL',
        '_HOL',
        '_NMR',
        '_NMAR',
        '_SEPR',
    );

    /**
     * Tags that don't require a PLAC subtag
     *
     * @var array
     */
    public static $NON_PLAC_FACTS = array(
        'ENDL',
        'NCHI',
        'REFN',
        'SLGC',
        'SLGS',
    );

    /**
     * Tags that don't require a DATE subtag
     *
     * @var array
     */
    public static $NON_DATE_FACTS = array(
        'ABBR',
        'ADDR',
        'AFN',
        'AUTH',
        'CHIL',
        'EMAIL',
        'FAX',
        'FILE',
        'HUSB',
        'NAME',
        'NCHI',
        'NOTE',
        'OBJE',
        'PHON',
        'PUBL',
        'REFN',
        'REPO',
        'RESN',
        'SEX',
        'SOUR',
        'SSN',
        'TEXT',
        'TITL',
        'WIFE',
        'WWW',
        '_EMAIL',
    );

    /**
     * Tags that require a DATE:TIME as well as a DATE
     *
     * @var array
     */
    public static $DATE_AND_TIME = array(
        'BIRT',
        'DEAT',
    );

    /**
     * Level 2 tags that apply to specific Level 1 tags
     * Tags are applied in the order they appear here.
     *
     * @var array
     */
    public static $LEVEL2_TAGS = array(
        '_HEB'     => array(
            'NAME',
            'TITL',
        ),
        'ROMN'     => array(
            'NAME',
            'TITL',
        ),
        'TYPE'     => array(
            'EVEN',
            'FACT',
            'GRAD',
            'IDNO',
            'MARR',
            'ORDN',
            'SSN',
        ),
        'AGNC'     => array(
            'EDUC',
            'GRAD',
            'OCCU',
            'ORDN',
            'RETI',
        ),
        'CALN'     => array(
            'REPO',
        ),
        'CEME'     => array( // CEME is NOT a valid 5.5.1 tag
                             //'BURI',
        ),
        'RELA'     => array(
            'ASSO',
            '_ASSO',
        ),
        'DATE'     => array(
            'ADOP',
            'ANUL',
            'BAPL',
            'BAPM',
            'BARM',
            'BASM',
            'BIRT',
            'BLES',
            'BURI',
            'CENS',
            'CENS',
            'CHR',
            'CHRA',
            'CONF',
            'CONL',
            'CREM',
            'DEAT',
            'DIV',
            'DIVF',
            'DSCR',
            'EDUC',
            'EMIG',
            'ENDL',
            'ENGA',
            'EVEN',
            'FCOM',
            'GRAD',
            'IMMI',
            'MARB',
            'MARC',
            'MARL',
            'MARR',
            'MARS',
            'NATU',
            'OCCU',
            'ORDN',
            'PROB',
            'PROP',
            'RELI',
            'RESI',
            'RETI',
            'SLGC',
            'SLGS',
            'WILL',
            '_TODO',
        ),
        'AGE'      => array(
            'CENS',
            'DEAT',
        ),
        'TEMP'     => array(
            'BAPL',
            'CONL',
            'ENDL',
            'SLGC',
            'SLGS',
        ),
        'PLAC'     => array(
            'ADOP',
            'ANUL',
            'BAPL',
            'BAPM',
            'BARM',
            'BASM',
            'BIRT',
            'BLES',
            'BURI',
            'CENS',
            'CHR',
            'CHRA',
            'CONF',
            'CONL',
            'CREM',
            'DEAT',
            'DIV',
            'DIVF',
            'EDUC',
            'EMIG',
            'ENDL',
            'ENGA',
            'EVEN',
            'FCOM',
            'GRAD',
            'IMMI',
            'MARB',
            'MARC',
            'MARL',
            'MARR',
            'MARS',
            'NATU',
            'OCCU',
            'ORDN',
            'PROB',
            'PROP',
            'RELI',
            'RESI',
            'RETI',
            'SLGC',
            'SLGS',
            'SSN',
            'WILL',
        ),
        'STAT'     => array(
            'BAPL',
            'CONL',
            'ENDL',
            'SLGC',
            'SLGS',
        ),
        'ADDR'     => array(
            'BAPM',
            'BIRT',
            'BURI',
            'CENS',
            'CHR',
            'CHRA',
            'CONF',
            'CREM',
            'DEAT',
            'EDUC',
            'EVEN',
            'GRAD',
            'MARR',
            'OCCU',
            'ORDN',
            'PROP',
            'RESI',
        ),
        'CAUS'     => array(
            'DEAT',
        ),
        'PHON'     => array(
            'OCCU',
            'RESI',
        ),
        'FAX'      => array(
            'OCCU',
            'RESI',
        ),
        'WWW'      => array(
            'OCCU',
            'RESI',
        ),
        'EMAIL'    => array(
            'OCCU',
            'RESI',
        ),
        'HUSB'     => array(
            'MARR',
        ),
        'WIFE'     => array(
            'MARR',
        ),
        'FAMC'     => array(
            'ADOP',
            'SLGC',
        ),
        'FILE'     => array(
            'OBJE',
        ),
        '_PRIM'    => array(
            'OBJE',
        ),
        'EVEN'     => array(
            'DATA',
        ),
        '_WT_USER' => array(
            '_TODO',
        ),
        // See https://bugs.launchpad.net/webtrees/+bug/1082666
        'RELI'     => array(
            //'CHR',
            //'CHRA',
            //'BAPM',
            //'MARR',
            //'BURI',
        ),
    );

}