<?php
namespace Webtrees\LegacyBundle\Legacy;

/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

use Fgt\Application;
use Fgt\Config;
use Fgt\Globals;
use PclZip;
use Zend_Session;

/**
 * The clippings cart.
 */
class ClippingsCart
{
    /** @var string */
    private $download_data;

    /** @var string[] List of files to include */
    private $media_list;

    /** @var string The type of the record being added */
    public $type;

    /** @var string The XREF of the record being added */
    public $id;

    /** @var string Whether to include media files for media objects */
    private $IncludeMedia;

    /** @var string The media path (if any) to prefix to the filenames */
    public $conv_path;

    /** @var string The privacy level to apply to the download */
    private $privatize_export;

    /** @var string Whether to download as ZIP file */
    private $Zip;

    /** @var integer The number of ancestor generations (individuals) to add */
    public $level1;

    /** @var integer The number of ancestor generations (families) to add */
    public $level2;

    /** @var integer The number of descendent generations to add */
    public $level3;

    /**
     * Create the clippings controller
     */
    public function __construct()
    {
        // Our cart is an array of items in the session
        if (!is_array(Application::i()->getSession()->cart)) {
            Application::i()->getSession()->cart = array();
        }
        if (!array_key_exists(WT_GED_ID, Application::i()->getSession()->cart)) {
            Application::i()->getSession()->cart[WT_GED_ID] = array();
        }

        $this->action           = Filter::get('action');
        $this->id               = Filter::get('id');
        $convert                = Filter::get('convert', 'yes|no', 'no');
        $this->Zip              = Filter::get('Zip');
        $this->IncludeMedia     = Filter::get('IncludeMedia');
        $this->conv_path        = Filter::get('conv_path');
        $this->privatize_export = Filter::get('privatize_export', 'none|visitor|user|gedadmin', 'visitor');
        $this->level1           = Filter::getInteger('level1');
        $this->level2           = Filter::getInteger('level2');
        $this->level3           = Filter::getInteger('level3');
        $others                 = Filter::get('others');
        $this->type             = Filter::get('type');

        if (($this->privatize_export === 'none' || $this->privatize_export === 'none') && !WT_USER_GEDCOM_ADMIN) {
            $this->privatize_export = 'visitor';
        }
        if ($this->privatize_export === 'user' && !WT_USER_CAN_ACCESS) {
            $this->privatize_export = 'visitor';
        }

        if ($this->action === 'add') {
            if (empty($this->type) && !empty($this->id)) {
                $obj = GedcomRecord::getInstance($this->id);
                if ($obj) {
                    $this->type = $obj::RECORD_TYPE;
                } else {
                    $this->type   = '';
                    $this->id     = '';
                    $this->action = '';
                }
            } elseif (empty($this->id)) {
                $this->action = '';
            }
            if (!empty($this->id) && $this->type !== 'FAM' && $this->type !== 'INDI' && $this->type !== 'SOUR') {
                $this->action = 'add1';
            }
        }

        if ($this->action === 'add1') {
            $obj = GedcomRecord::getInstance($this->id);
            $this->addClipping($obj);
            if ($this->type === 'SOUR') {
                if ($others === 'linked') {
                    foreach ($obj->linkedIndividuals('SOUR') as $indi) {
                        $this->addClipping($indi);
                    }
                    foreach ($obj->linkedFamilies('SOUR') as $fam) {
                        $this->addClipping($fam);
                    }
                }
            }
            if ($this->type === 'FAM') {
                if ($others === 'parents') {
                    $this->addClipping($obj->getHusband());
                    $this->addClipping($obj->getWife());
                } elseif ($others === "members") {
                    $this->addFamilyMembers(Family::getInstance($this->id));
                } elseif ($others === "descendants") {
                    $this->addFamilyDescendancy(Family::getInstance($this->id));
                }
            } elseif ($this->type === 'INDI') {
                if ($others === 'parents') {
                    foreach (Individual::getInstance($this->id)
                                       ->getChildFamilies() as $family) {
                        $this->addFamilyMembers($family);
                    }
                } elseif ($others === 'ancestors') {
                    $this->addAncestorsToCart(Individual::getInstance($this->id), $this->level1);
                } elseif ($others === 'ancestorsfamilies') {
                    $this->addAncestorsToCartFamilies(Individual::getInstance($this->id), $this->level2);
                } elseif ($others === 'members') {
                    foreach (Individual::getInstance($this->id)
                                       ->getSpouseFamilies() as $family) {
                        $this->addFamilyMembers($family);
                    }
                } elseif ($others === 'descendants') {
                    foreach (Individual::getInstance($this->id)
                                       ->getSpouseFamilies() as $family) {
                        $this->addClipping($family);
                        $this->addFamilyDescendancy($family, $this->level3);
                    }
                }
                uksort(Application::i()->getSession()->cart[WT_GED_ID], __NAMESPACE__ . '\ClippingsCart::compareClippings');
            }
        } elseif ($this->action === 'remove') {
            unset (Application::i()->getSession()->cart[WT_GED_ID][$this->id]);
        } elseif ($this->action === 'empty') {
            Application::i()->getSession()->cart[WT_GED_ID] = array();
        } elseif ($this->action === 'download') {
            $media      = array();
            $mediacount = 0;
            $filetext   = FunctionsExport::i()->gedcom_header(WT_GEDCOM);
            // Include SUBM/SUBN records, if they exist
            $subn =
                Database::i()->prepare("SELECT o_gedcom FROM `##other` WHERE o_type=? AND o_file=?")
                        ->execute(array(
                                      'SUBN',
                                      WT_GED_ID
                                  ))
                        ->fetchOne();
            if ($subn) {
                $filetext .= $subn . "\n";
            }
            $subm =
                Database::i()->prepare("SELECT o_gedcom FROM `##other` WHERE o_type=? AND o_file=?")
                        ->execute(array(
                                      'SUBM',
                                      WT_GED_ID
                                  ))
                        ->fetchOne();
            if ($subm) {
                $filetext .= $subm . "\n";
            }
            if ($convert === "yes") {
                $filetext = str_replace("UTF-8", "ANSI", $filetext);
                $filetext = utf8_decode($filetext);
            }

            switch ($this->privatize_export) {
                case 'gedadmin':
                    $access_level = WT_PRIV_NONE;
                    break;
                case 'user':
                    $access_level = WT_PRIV_USER;
                    break;
                case 'visitor':
                    $access_level = WT_PRIV_PUBLIC;
                    break;
                case 'none':
                    $access_level = WT_PRIV_HIDE;
                    break;
            }

            foreach (array_keys(Application::i()->getSession()->cart[WT_GED_ID]) as $xref) {
                $object = GedcomRecord::getInstance($xref);
                // The object may have been deleted since we added it to the cart....
                if ($object) {
                    $record = $object->privatizeGedcom($access_level);
                    // Remove links to objects that aren't in the cart
                    preg_match_all('/\n1 ' . WT_REGEX_TAG . ' @(' . WT_REGEX_XREF . ')@(\n[2-9].*)*/', $record, $matches, PREG_SET_ORDER);
                    foreach ($matches as $match) {
                        if (!array_key_exists($match[1], Application::i()->getSession()->cart[WT_GED_ID])) {
                            $record = str_replace($match[0], '', $record);
                        }
                    }
                    preg_match_all('/\n2 ' . WT_REGEX_TAG . ' @(' . WT_REGEX_XREF . ')@(\n[3-9].*)*/', $record, $matches, PREG_SET_ORDER);
                    foreach ($matches as $match) {
                        if (!array_key_exists($match[1], Application::i()->getSession()->cart[WT_GED_ID])) {
                            $record = str_replace($match[0], '', $record);
                        }
                    }
                    preg_match_all('/\n3 ' . WT_REGEX_TAG . ' @(' . WT_REGEX_XREF . ')@(\n[4-9].*)*/', $record, $matches, PREG_SET_ORDER);
                    foreach ($matches as $match) {
                        if (!array_key_exists($match[1], Application::i()->getSession()->cart[WT_GED_ID])) {
                            $record = str_replace($match[0], '', $record);
                        }
                    }
                    $record      = FunctionsExport::i()->convert_media_path($record, $this->conv_path);
                    $savedRecord = $record; // Save this for the "does this file exist" check
                    if ($convert === 'yes') {
                        $record = utf8_decode($record);
                    }
                    switch ($object::RECORD_TYPE) {
                        case 'INDI':
                            $filetext .= $record . "\n";
                            $filetext .= "1 SOUR @WEBTREES@\n";
                            $filetext .= "2 PAGE " . Config::get(Config::BASE_URL) . $object->getRawUrl() . "\n";
                            break;
                        case 'FAM':
                            $filetext .= $record . "\n";
                            $filetext .= "1 SOUR @WEBTREES@\n";
                            $filetext .= "2 PAGE " . Config::get(Config::BASE_URL) . $object->getRawUrl() . "\n";
                            break;
                        case 'SOUR':
                            $filetext .= $record . "\n";
                            $filetext .= "1 NOTE " . Config::get(Config::BASE_URL) . $object->getRawUrl() . "\n";
                            break;
                        default:
                            $ft              = preg_match_all("/\n\d FILE (.+)/", $savedRecord, $match, PREG_SET_ORDER);
                            $MEDIA_DIRECTORY = Globals::i()->WT_TREE->getPreference('MEDIA_DIRECTORY');
                            for ($k = 0; $k < $ft; $k++) {
                                // Skip external files and non-existant files
                                if (file_exists(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $match[$k][1])) {
                                    $media[$mediacount] = array(
                                        PCLZIP_ATT_FILE_NAME          => Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $match[$k][1],
                                        PCLZIP_ATT_FILE_NEW_FULL_NAME => $match[$k][1],
                                    );
                                    $mediacount++;
                                }
                            }
                            $filetext .= trim($record) . "\n";
                            break;
                    }
                }
            }

            if ($this->IncludeMedia === "yes") {
                $this->media_list = $media;
            } else {
                $this->media_list = array();
            }
            $filetext .= "0 @WEBTREES@ SOUR\n1 TITL " . Config::get(Config::BASE_URL) . "\n";
            if ($user_id = Globals::i()->WT_TREE->getPreference('CONTACT_EMAIL')) {
                $user = User::find($user_id);
                $filetext .= "1 AUTH " . $user->getRealName() . "\n";
            }
            $filetext .= "0 TRLR\n";
            //-- make sure the preferred line endings are used
            $filetext            = preg_replace("/[\r\n]+/", WT_EOL, $filetext);
            $this->download_data = $filetext;
            $this->downloadClipping();
        }
    }

    /**
     * Loads everything in the clippings cart into a zip file.
     */
    function zipCart()
    {
        $tempFileName = 'clipping' . rand() . '.ged';
        $fp           = fopen(Config::get(Config::DATA_DIRECTORY) . $tempFileName, "wb");
        if ($fp) {
            flock($fp, LOCK_EX);
            fwrite($fp, $this->download_data);
            flock($fp, LOCK_UN);
            fclose($fp);
            $zipName = "clippings" . rand(0, 1500) . ".zip";
            $fname   = Config::get(Config::DATA_DIRECTORY) . $zipName;
            $comment = "Created by " . WT_WEBTREES . " " . WT_VERSION . " on " . date("d M Y") . ".";
            $archive = new PclZip($fname);
            // add the ged file to the root of the zip file (strip off the data folder)
            $this->media_list[] = array(
                PCLZIP_ATT_FILE_NAME          => Config::get(Config::DATA_DIRECTORY) . $tempFileName,
                PCLZIP_ATT_FILE_NEW_FULL_NAME => $tempFileName
            );
            $v_list             = $archive->create($this->media_list, PCLZIP_OPT_COMMENT, $comment);
            if ($v_list == 0) {
                echo "Error : " . $archive->errorInfo(true) . "</td></tr>";
            } else {
                $openedFile          = fopen($fname, "rb");
                $this->download_data = fread($openedFile, filesize($fname));
                fclose($openedFile);
                unlink($fname);
            }
            unlink(Config::get(Config::DATA_DIRECTORY) . $tempFileName);
        } else {
            echo I18N::translate('Cannot create') . " " . Config::get(Config::DATA_DIRECTORY) . "$tempFileName " . I18N::translate('Check the access rights on this folder.') . "<br><br>";
        }
    }

    /**
     * Brings up the download dialog box and allows the user to download the file
     * based on the options he or she selected.
     */
    function downloadClipping()
    {
        Zend_Session::writeClose();

        if ($this->IncludeMedia === 'yes' || $this->Zip === 'yes') {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="clipping.zip"');
            $this->zipCart();
        } else {
            header('Content-Type: text/plain');
            header('Content-Disposition: attachment; filename="clipping.ged"');
        }

        header('Content-length: ' . strlen($this->download_data));
        echo $this->download_data;
        exit;
    }

    /**
     * Inserts a clipping into the clipping cart
     *
     * @param GedcomRecord $record
     */
    function addClipping(GedcomRecord $record)
    {
        if ($record->canShowName()) {
            Application::i()->getSession()->cart[WT_GED_ID][$record->getXref()] = true;
            // Add directly linked records
            preg_match_all('/\n\d (?:OBJE|NOTE|SOUR|REPO) @(' . WT_REGEX_XREF . ')@/', $record->getGedcom(), $matches);
            foreach ($matches[1] as $match) {
                Application::i()->getSession()->cart[WT_GED_ID][$match] = true;
            }
        }
    }

    /**
     * Recursive function to traverse the tree
     *
     * @param Family|null $family
     * @param integer     $level
     */
    function addFamilyDescendancy(Family $family = null, $level = PHP_INT_MAX)
    {
        if (!$family) {
            return;
        }
        foreach ($family->getSpouses() as $spouse) {
            $this->addClipping($spouse);
        }
        foreach ($family->getChildren() as $child) {
            $this->addClipping($child);
            foreach ($child->getSpouseFamilies() as $child_family) {
                $this->addClipping($child_family);
                if ($level > 0) {
                    $this->addFamilyDescendancy($child_family, $level - 1); // recurse on the childs family
                }
            }
        }
    }

    /**
     * Add a family, and all its members
     *
     * @param Family|null $family
     */
    function addFamilyMembers(Family $family = null)
    {
        if (!$family) {
            return;
        }
        $this->addClipping($family);
        foreach ($family->getSpouses() as $spouse) {
            $this->addClipping($spouse);
        }
        foreach ($family->getChildren() as $child) {
            $this->addClipping($child);
        }
    }

    /**
     * Recursively add direct-line ancestors to cart
     *
     * @param Individual|null $person
     * @param integer         $level
     */
    function addAncestorsToCart(Individual $person = null, $level = 0)
    {
        if (!$person) {
            return;
        }
        $this->addClipping($person);
        if ($level > 0) {
            foreach ($person->getChildFamilies() as $family) {
                $this->addClipping($family);
                $this->addAncestorsToCart($family->getHusband(), $level - 1);
                $this->addAncestorsToCart($family->getWife(), $level - 1);
            }
        }
    }

    /**
     * Recursively adds direct-line ancestors and their families to the cart
     *
     * @param Individual|null $person
     * @param integer         $level
     */
    function addAncestorsToCartFamilies(Individual $person = null, $level = 0)
    {
        if (!$person) {
            return;
        }
        if ($level > 0) {
            foreach ($person->getChildFamilies() as $family) {
                $this->addFamilyMembers($family);
                $this->addAncestorsToCartFamilies($family->getHusband(), $level - 1);
                $this->addAncestorsToCartFamilies($family->getWife(), $level - 1);
            }
        }
    }

    /**
     * Helper function to sort records by type/name
     *
     * @param string $a
     * @param string $b
     *
     * @return integer
     */
    static function compareClippings($a, $b)
    {
        $a = GedcomRecord::getInstance($a);
        $b = GedcomRecord::getInstance($b);
        if ($a && $b) {
            switch ($a::RECORD_TYPE) {
                case 'INDI':
                    $t1 = 1;
                    break;
                case 'FAM':
                    $t1 = 2;
                    break;
                case 'SOUR':
                    $t1 = 3;
                    break;
                case 'REPO':
                    $t1 = 4;
                    break;
                case 'OBJE':
                    $t1 = 5;
                    break;
                case 'NOTE':
                    $t1 = 6;
                    break;
                default:
                    $t1 = 7;
                    break;
            }
            switch ($b::RECORD_TYPE) {
                case 'INDI':
                    $t2 = 1;
                    break;
                case 'FAM':
                    $t2 = 2;
                    break;
                case 'SOUR':
                    $t2 = 3;
                    break;
                case 'REPO':
                    $t2 = 4;
                    break;
                case 'OBJE':
                    $t2 = 5;
                    break;
                case 'NOTE':
                    $t2 = 6;
                    break;
                default:
                    $t2 = 7;
                    break;
            }
            if ($t1 != $t2) {
                return $t1 - $t2;
            } else {
                return GedcomRecord::compare($a, $b);
            }
        } else {
            return 0;
        }
    }
}
