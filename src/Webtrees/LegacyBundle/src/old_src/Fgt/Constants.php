<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Fgt;


use FamGenTree\AppBundle\Context\Configuration\Domain\ConfigKeys;
use FamGenTree\AppBundle\Context\Configuration\Domain\FgtConfig;
use Webtrees\LegacyBundle\Legacy\I18N;

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

    public static function defineCommonConstants(FgtConfig $config)
    {
// Identify ourself
        define('WT_WEBTREES', $config->get(ConfigKeys::SYSTEM_NAME)->getValue());
        define('WT_VERSION', $config->get(ConfigKeys::SYSTEM_VERSION)->getValue());

// External URLs
        define('WT_WEBTREES_URL', $config->get(ConfigKeys::PROJECT_HOMEPAGE_URL)->getValue());
        define('WT_WEBTREES_WIKI', $config->get(ConfigKeys::PROJECT_WIKI_HOMEPAGE_URL)->getValue());

// Resources have version numbers in the URL, so that they can be cached indefinitely.
        define('WT_STATIC_URL', getenv('STATIC_URL')); // We could set this to load our own static resources from a cookie-free domain.

        if (getenv('USE_CDN')) {
            foreach (array(
                         'WT_BOOTSTRAP_CSS_URL',
                         'WT_BOOTSTRAP_DATETIMEPICKER_CSS_URL',
                         'WT_BOOTSTRAP_DATETIMEPICKER_JS_URL',
                         'WT_BOOTSTRAP_JS_URL',
                         'WT_BOOTSTRAP_RTL_CSS_URL',
                         'WT_DATATABLES_BOOTSTRAP_CSS_URL',
                         'WT_DATATABLES_BOOTSTRAP_JS_URL',
                         'WT_FONT_AWESOME_CSS_URL',
                         'WT_JQUERYUI_JS_URL',
                         'WT_JQUERY_COOKIE_JS_URL',
                         'WT_JQUERY_DATATABLES_JS_URL',
                         'WT_JQUERY_JS_URL',
                         'WT_MODERNIZR_JS_URL',
                         'WT_MOMENT_JS_URL',
                         'WT_RESPOND_JS_URL'
                     ) as $constant) {
                define($constant, $config->get("theme.webtrees.cdn.remote.{$constant}")->getValue());
            }
        } else {
            foreach (array(
                         'WT_BOOTSTRAP_CSS_URL',
                         'WT_BOOTSTRAP_DATETIMEPICKER_CSS_URL',
                         'WT_BOOTSTRAP_DATETIMEPICKER_JS_URL',
                         'WT_BOOTSTRAP_JS_URL',
                         'WT_BOOTSTRAP_RTL_CSS_URL',
                         'WT_DATATABLES_BOOTSTRAP_CSS_URL',
                         'WT_DATATABLES_BOOTSTRAP_JS_URL',
                         'WT_FONT_AWESOME_CSS_URL',
                         'WT_JQUERYUI_JS_URL',
                         'WT_JQUERY_COOKIE_JS_URL',
                         'WT_JQUERY_DATATABLES_JS_URL',
                         'WT_JQUERY_JS_URL',
                         'WT_MODERNIZR_JS_URL',
                         'WT_MOMENT_JS_URL',
                         'WT_RESPOND_JS_URL'
                     ) as $constant) {
                define($constant, sprintf(
                    $config->get("theme.webtrees.cdn.remote.{$constant}")->getValue(),
                    WT_STATIC_URL
                ));
            }
        }

// Location of our modules and themes.  These are used as URLs and folder paths.
        define('WT_MODULES_DIR', 'modules_v3/'); // Update setup.php and build/Makefile when this changes
        define('WT_THEMES_DIR', 'themes/');

// Enable debugging output?
        define('WT_DEBUG', false);

// Required version of database tables/columns/indexes/etc.
        define('WT_SCHEMA_VERSION', 29);

// Regular expressions for validating user input, etc.
        define('WT_MINIMUM_PASSWORD_LENGTH', 6);
        define('WT_REGEX_XREF', '[A-Za-z0-9:_-]+');
        define('WT_REGEX_TAG', '[_A-Z][_A-Z0-9]*');
        define('WT_REGEX_INTEGER', '-?\d+');
        define('WT_REGEX_BYTES', '[0-9]+[bBkKmMgG]?');
        define('WT_REGEX_IPV4', '\d{1,3}(\.\d{1,3}){3}');
        define('WT_REGEX_USERNAME', '[^<>"%{};]+');
        define('WT_REGEX_PASSWORD', '.{' . WT_MINIMUM_PASSWORD_LENGTH . ',}');

// UTF8 representation of various characters
        define('WT_UTF8_BOM', "\xEF\xBB\xBF"); // U+FEFF (Byte order mark)
        define('WT_UTF8_LRM', "\xE2\x80\x8E"); // U+200E (Left to Right mark:  zero-width character with LTR directionality)
        define('WT_UTF8_RLM', "\xE2\x80\x8F"); // U+200F (Right to Left mark:  zero-width character with RTL directionality)
        define('WT_UTF8_LRO', "\xE2\x80\xAD"); // U+202D (Left to Right override: force everything following to LTR mode)
        define('WT_UTF8_RLO', "\xE2\x80\xAE"); // U+202E (Right to Left override: force everything following to RTL mode)
        define('WT_UTF8_LRE', "\xE2\x80\xAA"); // U+202A (Left to Right embedding: treat everything following as LTR text)
        define('WT_UTF8_RLE', "\xE2\x80\xAB"); // U+202B (Right to Left embedding: treat everything following as RTL text)
        define('WT_UTF8_PDF', "\xE2\x80\xAC"); // U+202C (Pop directional formatting: restore state prior to last LRO, RLO, LRE, RLE)

// Alternatives to BMD events for lists, charts, etc.
        define('WT_EVENTS_BIRT', 'BIRT|CHR|BAPM|_BRTM|ADOP');
        define('WT_EVENTS_DEAT', 'DEAT|BURI|CREM');
        define('WT_EVENTS_MARR', 'MARR|_NMR');
        define('WT_EVENTS_DIV', 'DIV|ANUL|_SEPR');

// Use these line endings when writing files on the server
        define('WT_EOL', "\r\n");

// Gedcom specification/definitions
        define('WT_GEDCOM_LINE_LENGTH', 255 - strlen(WT_EOL)); // Characters, not bytes

// Used in Google charts
        define('WT_GOOGLE_CHART_ENCODING', 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.');

// Privacy constants
        define('WT_PRIV_PUBLIC', 2); // Allows visitors to view the marked information
        define('WT_PRIV_USER', 1); // Allows members to access the marked information
        define('WT_PRIV_NONE', 0); // Allows managers to access the marked information
        define('WT_PRIV_HIDE', -1); // Hide the item to all users
    }

}