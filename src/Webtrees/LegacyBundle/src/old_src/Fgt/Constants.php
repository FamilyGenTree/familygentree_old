<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace Fgt;


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

    public static function defineCommonConstants()
    {
// Identify ourself
        define('WT_WEBTREES', 'webtrees');
        define('WT_VERSION', '1.7.0-dev');

// External URLs
        define('WT_WEBTREES_URL', 'http://www.webtrees.net/');
        define('WT_WEBTREES_WIKI', 'http://wiki.webtrees.net/');

// Resources have version numbers in the URL, so that they can be cached indefinitely.
        define('WT_STATIC_URL', getenv('STATIC_URL')); // We could set this to load our own static resources from a cookie-free domain.

        if (getenv('USE_CDN')) {
            // Caution, using a CDN will break support for responsive features in IE8, as respond.js
            // needs to be on the same domain as all the CSS files.
            define('WT_BOOTSTRAP_CSS_URL', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/css/bootstrap.min.css');
            define('WT_BOOTSTRAP_DATETIMEPICKER_CSS_URL', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.0.0/js/bootstrap-datetimepicker.min.css');
            define('WT_BOOTSTRAP_DATETIMEPICKER_JS_URL', '//cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.0.0/css/bootstrap-datetimepicker.js');
            define('WT_BOOTSTRAP_JS_URL', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js');
            define('WT_BOOTSTRAP_RTL_CSS_URL', '//cdn.rawgit.com/morteza/bootstrap-rtl/master/dist/cdnjs/3.3.1/css/bootstrap-rtl.min.css'); // Cloudflare is out of date
            define('WT_DATATABLES_BOOTSTRAP_CSS_URL', '//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.css');
            define('WT_DATATABLES_BOOTSTRAP_JS_URL', '//cdn.datatables.net/plug-ins/3cfcc339e89/integration/bootstrap/3/dataTables.bootstrap.js');
            define('WT_FONT_AWESOME_CSS_URL', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.min.css');
            define('WT_JQUERYUI_JS_URL', '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js');
            define('WT_JQUERY_COOKIE_JS_URL', '//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js');
            define('WT_JQUERY_DATATABLES_JS_URL', '//cdnjs.cloudflare.com/ajax/libs/datatables/1.10.4/js/jquery.dataTables.min.js');
            define('WT_JQUERY_JS_URL', '//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.min.js');
            define('WT_MODERNIZR_JS_URL', '//cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js');
            define('WT_MOMENT_JS_URL', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.min.js');
            define('WT_RESPOND_JS_URL', '//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js');
        } else {
            define('WT_BOOTSTRAP_CSS_URL', WT_STATIC_URL . 'packages/bootstrap-3.3.2/css/bootstrap.min.css');
            define('WT_BOOTSTRAP_DATETIMEPICKER_CSS_URL', WT_STATIC_URL . 'packages/bootstrap-datetimepicker-4.0.0/bootstrap-datetimepicker.min.css');
            define('WT_BOOTSTRAP_DATETIMEPICKER_JS_URL', WT_STATIC_URL . 'packages/bootstrap-datetimepicker-4.0.0/bootstrap-datetimepicker.min.js');
            define('WT_BOOTSTRAP_JS_URL', WT_STATIC_URL . 'packages/bootstrap-3.3.2/js/bootstrap.min.js');
            define('WT_BOOTSTRAP_RTL_CSS_URL', WT_STATIC_URL . 'packages/bootstrap-rtl-3.3.1/css/bootstrap-rtl.min.css');
            define('WT_DATATABLES_BOOTSTRAP_CSS_URL', WT_STATIC_URL . 'packages/datatables-1.10.4/plugins/dataTables.bootstrap.css');
            define('WT_DATATABLES_BOOTSTRAP_JS_URL', WT_STATIC_URL . 'packages/datatables-1.10.4/plugins/dataTables.bootstrap.js');
            define('WT_FONT_AWESOME_CSS_URL', WT_STATIC_URL . 'packages/font-awesome-4.3.0/css/font-awesome.min.css');
            define('WT_JQUERYUI_JS_URL', WT_STATIC_URL . 'packages/jquery-ui-1.11.2/js/jquery-ui.min.js');
            define('WT_JQUERY_COOKIE_JS_URL', WT_STATIC_URL . 'packages/jquery-cookie-1.4.1/jquery.cookie.js');
            define('WT_JQUERY_DATATABLES_JS_URL', WT_STATIC_URL . 'packages/datatables-1.10.4/js/jquery.dataTables.min.js');
            define('WT_JQUERY_JS_URL', WT_STATIC_URL . 'packages/jquery-1.11.2/jquery.min.js');
            define('WT_MODERNIZR_JS_URL', WT_STATIC_URL . 'packages/modernizr-2.8.3/modernizr.min.js');
            define('WT_MOMENT_JS_URL', WT_STATIC_URL . 'packages/moment-2.9.0/moment-with-locales.min.js');
            define('WT_RESPOND_JS_URL', WT_STATIC_URL . 'packages/respond-1.4.2/respond.min.js');
        }

// We can't load these from a CDN, as these have been patched.
        define('WT_JQUERY_COLORBOX_URL', WT_STATIC_URL . 'assets/js-1.7.0/jquery.colorbox-1.5.14.js');
        define('WT_JQUERY_WHEELZOOM_URL', WT_STATIC_URL . 'assets/js-1.7.0/jquery.wheelzoom-2.0.0.js');
        define('WT_CKEDITOR_BASE_URL', WT_STATIC_URL . 'packages/ckeditor-4.4.7-custom/');

// Location of our own scripts
        define('WT_ADMIN_JS_URL', WT_STATIC_URL . 'assets/js-1.7.0/admin.js');
        define('WT_AUTOCOMPLETE_JS_URL', WT_STATIC_URL . 'assets/js-1.7.0/autocomplete.js');
        define('WT_WEBTREES_JS_URL', WT_STATIC_URL . 'assets/js-1.7.0/webtrees.js');

// Location of our modules and themes.  These are used as URLs and folder paths.
        define('WT_MODULES_DIR', 'modules_v3/'); // Update setup.php and build/Makefile when this changes
        define('WT_THEMES_DIR', 'themes/');

// Enable debugging output?
        define('WT_DEBUG', false);
        define('WT_DEBUG_SQL', false);

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