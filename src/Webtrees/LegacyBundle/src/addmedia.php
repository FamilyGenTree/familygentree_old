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
use Fgt\UrlConstants;

define('WT_SCRIPT_NAME', UrlConstants::ADDMEDIA_PHP);
Application::i()->init()->started();

$NO_UPDATE_CHAN  = Globals::i()->WT_TREE->getPreference('NO_UPDATE_CHAN');
$MEDIA_DIRECTORY = Globals::i()->WT_TREE->getPreference('MEDIA_DIRECTORY');

$pid      = Filter::get('pid', WT_REGEX_XREF, Filter::post('pid', WT_REGEX_XREF)); // edit this media object
$linktoid = Filter::get('linktoid', WT_REGEX_XREF, Filter::post('linktoid', WT_REGEX_XREF)); // create a new media object, linked to this record
$action   = Filter::get('action', null, Filter::post('action'));
$filename = Filter::get('filename', null, Filter::post('filename'));
$text     = Filter::postArray('text');
$tag      = Filter::postArray('tag', WT_REGEX_TAG);
$islink   = Filter::postArray('islink');
$glevels  = Filter::postArray('glevels', '[0-9]');

$folder      = Filter::post('folder');
$update_CHAN = !Filter::postBool('preserve_last_changed');

$controller = Application::i()->setActiveController(new SimpleController());
$controller
    ->addExternalJavascript(WT_STATIC_URL.WebtreesTheme::WT_AUTOCOMPLETE_JS_URL)
    ->addInlineJavascript('autocomplete();')
    ->restrictAccess(Auth::isMember());

$disp  = true;
$media = Media::getInstance($pid);
if ($media) {
    $disp = $media->canShow();
}
if ($action == 'update' || $action == 'create') {
    if ($linktoid) {
        $disp = GedcomRecord::getInstance($linktoid)
                            ->canShow();
    }
}

if (!WT_USER_CAN_EDIT || !$disp) {
    $controller
        ->pageHeader()
        ->addInlineJavascript('closePopupAndReloadParent();');

    return;
}

// TODO - there is a lot of common code in the create and update cases....
// .... and also in the admin_media_upload.php script

switch ($action) {
    case 'create': // Save the information from the “showcreateform” action
        $controller->setPageTitle(I18N::translate('Create a new media object'));

        // Validate the media folder
        $folderName = str_replace('\\', '/', $folder);
        $folderName = trim($folderName, '/');
        if ($folderName == '.') {
            $folderName = '';
        }
        if ($folderName) {
            $folderName .= '/';
            // Not allowed to use “../”
            if (strpos('/' . $folderName, '/../') !== false) {
                FlashMessages::addMessage('Folder names are not allowed to include “../”');
                break;
            }
        }

        // Make sure the media folder exists
        if (!is_dir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY)) {
            if (File::mkdir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY)) {
                FlashMessages::addMessage(I18N::translate('The folder %s has been created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . '</span>'));
            } else {
                FlashMessages::addMessage(I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . '</span>'));
                break;
            }
        }

        // Managers can create new media paths (subfolders).  Users must use existing folders.
        if ($folderName && !is_dir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $folderName)) {
            if (WT_USER_GEDCOM_ADMIN) {
                if (File::mkdir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $folderName)) {
                    FlashMessages::addMessage(I18N::translate('The folder %s has been created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $folderName . '</span>'));
                } else {
                    FlashMessages::addMessage(I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $folderName . '</span>'));
                    break;
                }
            } else {
                // Regular users should not have seen this option - so no need for an error message.
                break;
            }
        }

        // The media folder exists.  Now create a thumbnail folder to match it.
        if (!is_dir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . 'thumbs/' . $folderName)) {
            if (!File::mkdir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . 'thumbs/' . $folderName)) {
                FlashMessages::addMessage(I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . 'thumbs/' . $folderName . '</span>'));
                break;
            }
        }

        // A thumbnail file with no main image?
        if (!empty($_FILES['thumbnail']['name']) && empty($_FILES['mediafile']['name'])) {
            // Assume the user used the wrong field, and treat this as a main image
            $_FILES['mediafile'] = $_FILES['thumbnail'];
            unset($_FILES['thumbnail']);
        }

        // Thumbnail files must contain images.
        if (!empty($_FILES['thumbnail']['name']) && !preg_match('/^image/', $_FILES['thumbnail']['type'])) {
            FlashMessages::addMessage(I18N::translate('Thumbnail files must contain images.'));
            break;
        }

        // User-specified filename?
        if ($tag[0] == 'FILE' && $text[0]) {
            $filename = $text[0];
        }
        // Use the name of the uploaded file?
        // If no filename specified, use the name of the uploaded file?
        if (!$filename && !empty($_FILES['mediafile']['name'])) {
            $filename = $_FILES['mediafile']['name'];
        }

        // Validate the media path and filename
        if (preg_match('/^https?:\/\//i', $text[0], $match)) {
            // External media needs no further validation
            $fileName   = $filename;
            $folderName = '';
            unset($_FILES['mediafile'], $_FILES['thumbnail']);
        } elseif (preg_match('/([\/\\\\<>])/', $filename, $match)) {
            // Local media files cannot contain certain special characters
            FlashMessages::addMessage(I18N::translate('Filenames are not allowed to contain the character “%s”.', $match[1]));
            $filename = '';
            break;
        } elseif (preg_match('/(\.(php|pl|cgi|bash|sh|bat|exe|com|htm|html|shtml))$/i', $filename, $match)) {
            // Do not allow obvious script files.
            FlashMessages::addMessage(I18N::translate('Filenames are not allowed to have the extension “%s”.', $match[1]));
            $filename = '';
            break;
        } elseif (!$filename) {
            FlashMessages::addMessage(I18N::translate('No media file was provided.'));
            break;
        } else {
            $fileName = $filename;
        }

        // Now copy the file to the correct location.
        if (!empty($_FILES['mediafile']['name'])) {
            $serverFileName = Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $folderName . $fileName;
            if (file_exists($serverFileName)) {
                FlashMessages::addMessage(I18N::translate('The file %s already exists.  Use another filename.', $folderName . $fileName));
                $filename = '';
                break;
            }
            if (move_uploaded_file($_FILES['mediafile']['tmp_name'], $serverFileName)) {
                Log::addMediaLog('Media file ' . $serverFileName . ' uploaded');
            } else {
                FlashMessages::addMessage(
                    I18N::translate('There was an error uploading your file.') .
                    '<br>' .
                    Functions::i()->file_upload_error_text($_FILES['mediafile']['error'])
                );
                $filename = '';
                break;
            }

            // Now copy the (optional) thumbnail
            if (!empty($_FILES['thumbnail']['name']) && preg_match('/^image\/(png|gif|jpeg)/', $_FILES['thumbnail']['type'], $match)) {
                // Thumbnails have either
                // (a) the same filename as the main image
                // (b) the same filename as the main image - but with a .png extension
                if ($match[1] == 'png' && !preg_match('/\.(png)$/i', $fileName)) {
                    $thumbFile = preg_replace('/\.[a-z0-9]{3,5}$/', '.png', $fileName);
                } else {
                    $thumbFile = $fileName;
                }
                $serverFileName = Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . 'thumbs/' . $folderName . $thumbFile;
                if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $serverFileName)) {
                    Log::addMediaLog('Thumbnail file ' . $serverFileName . ' uploaded');
                }
            }
        }

        $controller->pageHeader();
        // Build the gedcom record
        $newged = "0 @new@ OBJE";
        if ($tag[0] == 'FILE') {
            // The admin has an edit field to change the filename
            $text[0] = $folderName . $fileName;
        } else {
            // Users keep the original filename
            $newged .= "\n1 FILE " . $folderName . $fileName;
        }

        $newged = FunctionsEdit::i()->handle_updates($newged);

        $new_media = GedcomRecord::createRecord($newged, WT_GED_ID);
        if ($linktoid) {
            $record = GedcomRecord::getInstance($linktoid);
            $record->createFact('1 OBJE @' . $new_media->getXref() . '@', true);
            Log::addEditLog('Media ID ' . $new_media->getXref() . " successfully added to $linktoid.");
            $controller->addInlineJavascript('closePopupAndReloadParent();');
        } else {
            Log::addEditLog('Media ID ' . $new_media->getXref() . ' successfully added.');
            $controller->addInlineJavascript('openerpasteid("' . $new_media->getXref() . '");');
        }
        echo '<button onclick="closePopupAndReloadParent();">', I18N::translate('close'), '</button>';

        return;

    case 'update': // Save the information from the “editmedia” action
        $controller->setPageTitle(I18N::translate('Edit media object'));

        // Validate the media folder
        $folderName = str_replace('\\', '/', $folder);
        $folderName = trim($folderName, '/');
        if ($folderName == '.') {
            $folderName = '';
        }
        if ($folderName) {
            $folderName .= '/';
            // Not allowed to use “../”
            if (strpos('/' . $folderName, '/../') !== false) {
                FlashMessages::addMessage('Folder names are not allowed to include “../”');
                break;
            }
        }

        // Make sure the media folder exists
        if (!is_dir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY)) {
            if (File::mkdir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY)) {
                FlashMessages::addMessage(I18N::translate('The folder %s has been created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . '</span>'));
            } else {
                FlashMessages::addMessage(I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . '</span>'));
                break;
            }
        }

        // Managers can create new media paths (subfolders).  Users must use existing folders.
        if ($folderName && !is_dir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $folderName)) {
            if (WT_USER_GEDCOM_ADMIN) {
                if (File::mkdir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $folderName)) {
                    FlashMessages::addMessage(I18N::translate('The folder %s has been created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $folderName . '</span>'));
                } else {
                    FlashMessages::addMessage(I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . $folderName . '</span>'));
                    break;
                }
            } else {
                // Regular users should not have seen this option - so no need for an error message.
                break;
            }
        }

        // The media folder exists.  Now create a thumbnail folder to match it.
        if (!is_dir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . 'thumbs/' . $folderName)) {
            if (!File::mkdir(Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . 'thumbs/' . $folderName)) {
                FlashMessages::addMessage(I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . Config::get(Config::DATA_DIRECTORY) . $MEDIA_DIRECTORY . 'thumbs/' . $folderName . '</span>'));
                break;
            }
        }

        // Validate the media path and filename
        if (preg_match('/^https?:\/\//i', $filename, $match)) {
            // External media needs no further validation
            $fileName   = $filename;
            $folderName = '';
            unset($_FILES['mediafile'], $_FILES['thumbnail']);
        } elseif (preg_match('/([\/\\\\<>])/', $filename, $match)) {
            // Local media files cannot contain certain special characters
            FlashMessages::addMessage(I18N::translate('Filenames are not allowed to contain the character “%s”.', $match[1]));
            $filename = '';
            break;
        } elseif (preg_match('/(\.(php|pl|cgi|bash|sh|bat|exe|com|htm|html|shtml))$/i', $filename, $match)) {
            // Do not allow obvious script files.
            FlashMessages::addMessage(I18N::translate('Filenames are not allowed to have the extension “%s”.', $match[1]));
            $filename = '';
            break;
        } elseif (!$filename) {
            FlashMessages::addMessage(I18N::translate('No media file was provided.'));
            break;
        } else {
            $fileName = $filename;
        }

        $oldFilename = $media->getFilename();
        $newFilename = $folderName . $fileName;

        // Cannot rename local to external or vice-versa
        if (Functions::i()->isFileExternal($oldFilename) != Functions::i()->isFileExternal($filename)) {
            FlashMessages::addMessage(I18N::translate('The media file %1$s could not be renamed to %2$s.', '<span class="filename">' . $oldFilename . '</span>', '<span class="filename">' . $newFilename . '</span>'));
            break;
        }

        $messages = false;
        // Move files on disk (if we can) to reflect the change to the GEDCOM data
        if (!$media->isExternal()) {
            $oldServerFile  = $media->getServerFilename('main');
            $oldServerThumb = $media->getServerFilename('thumb');

            $newmedia       = new Media("xxx", "0 @xxx@ OBJE\n1 FILE " . $newFilename, null, WT_GED_ID);
            $newServerFile  = $newmedia->getServerFilename('main');
            $newServerThumb = $newmedia->getServerFilename('thumb');

            // We could be either renaming an existing file, or updating a record (with no valid file) to point to a new file
            if ($oldServerFile != $newServerFile) {
                //-- check if the file is used in more than one gedcom
                //-- do not allow it to be moved or renamed if it is
                if (!$media->isExternal() && FunctionsDbPhp::i()->is_media_used_in_other_gedcom($media->getFilename(), WT_GED_ID)) {
                    FlashMessages::addMessage(I18N::translate('This file is linked to another family tree on this server.  It cannot be deleted, moved, or renamed until these links have been removed.'));
                    break;
                }

                if (!file_exists($newServerFile) || @md5_file($oldServerFile) == md5_file($newServerFile)) {
                    if (@rename($oldServerFile, $newServerFile)) {
                        FlashMessages::addMessage(I18N::translate('The media file %1$s has been renamed to %2$s.', '<span class="filename">' . $oldFilename . '</span>', '<span class="filename">' . $newFilename . '</span>'));
                    } else {
                        FlashMessages::addMessage(I18N::translate('The media file %1$s could not be renamed to %2$s.', '<span class="filename">' . $oldFilename . '</span>', '<span class="filename">' . $newFilename . '</span>'));
                    }
                    $messages = true;
                }
                if (!file_exists($newServerFile)) {
                    FlashMessages::addMessage(I18N::translate('The media file %s does not exist.', '<span class="filename">' . $newFilename . '</span>'));
                    $messages = true;
                }
            }
            if ($oldServerThumb != $newServerThumb) {
                if (!file_exists($newServerThumb) || @md5_file($oldServerFile) == md5_file($newServerThumb)) {
                    if (@rename($oldServerThumb, $newServerThumb)) {
                        FlashMessages::addMessage(I18N::translate('The thumbnail file %1$s has been renamed to %2$s.', '<span class="filename">' . $oldFilename . '</span>', '<span class="filename">' . $newFilename . '</span>'));
                    } else {
                        FlashMessages::addMessage(I18N::translate('The thumbnail file %1$s could not be renamed to %2$s.', '<span class="filename">' . $oldFilename . '</span>', '<span class="filename">' . $newFilename . '</span>'));
                    }
                    $messages = true;
                }
                if (!file_exists($newServerThumb)) {
                    FlashMessages::addMessage(I18N::translate('The thumbnail file %s does not exist.', '<span class="filename">' . $newFilename . '</span>'));
                    $messages = true;
                }
            }
        }

        // Insert the 1 FILE xxx record into the arrays used by function FunctionsEdit::i()->handle_updates()
        $glevels = array_merge(array('1'), $glevels);
        $tag     = array_merge(array('FILE'), $tag);
        $islink  = array_merge(array(0), $islink);
        $text    = array_merge(array($newFilename), $text);

        $record = GedcomRecord::getInstance($pid);
        $newrec = "0 @$pid@ OBJE\n";
        $newrec = FunctionsEdit::i()->handle_updates($newrec);
        $record->updateRecord($newrec, $update_CHAN);

        if ($pid && $linktoid) {
            $record = GedcomRecord::getInstance($linktoid);
            $record->createFact('1 OBJE @' . $pid . '@', true);
            Log::addEditLog('Media ID ' . $pid . " successfully added to $linktoid.");
        }
        $controller->pageHeader();
        if ($messages) {
            echo '<button onclick="closePopupAndReloadParent();">', I18N::translate('close'), '</button>';
        } else {
            $controller->addInlineJavascript('closePopupAndReloadParent();');
        }

        return;
    case 'showmediaform':
        $controller->setPageTitle(I18N::translate('Create a new media object'));
        $action = 'create';
        break;
    case 'editmedia':
        $controller->setPageTitle(I18N::translate('Edit media object'));
        $action = 'update';
        break;
    default:
        throw new \Exception('Bad $action (' . $action . ') in addmedia.php');
}

$controller->pageHeader();

echo '<div id="addmedia-page">'; //container for media edit pop-up
echo '<form method="post" name="newmedia" action="addmedia.php" enctype="multipart/form-data">';
echo '<input type="hidden" name="action" value="', $action, '">';
echo '<input type="hidden" name="ged" value="', WT_GEDCOM, '">';
echo '<input type="hidden" name="pid" value="', $pid, '">';
if ($linktoid) {
    echo '<input type="hidden" name="linktoid" value="', $linktoid, '">';
}
echo '<table class="facts_table">';
echo '<tr><td class="topbottombar" colspan="2">';
echo $controller->getPageTitle(), FunctionsPrint::i()->help_link('OBJE');
echo '</td></tr>';
if (!$linktoid && $action == 'create') {
    echo '<tr><td class="descriptionbox wrap width25">';
    echo I18N::translate('Enter an individual, family, or source ID');
    echo '</td><td class="optionbox wrap"><input type="text" data-autocomplete-type="IFS" name="linktoid" id="linktoid" size="6" value="">';
    echo ' ', FunctionsPrint::i()->print_findindi_link('linktoid');
    echo ' ', FunctionsPrint::i()->print_findfamily_link('linktoid');
    echo ' ', FunctionsPrint::i()->print_findsource_link('linktoid');
    echo '<p class="sub">', I18N::translate('Enter or search for the ID of the individual, family, or source to which this media item should be linked.'), '</p></td></tr>';
}

if ($media) {
    $gedrec = $media->getGedcom();
} else {
    $gedrec = '';
}

// 1 FILE
if (preg_match('/\n\d (FILE.*)/', $gedrec, $match)) {
    $gedfile = $match[1];
} else {
    $gedfile = 'FILE';
}

if ($gedfile == 'FILE') {
    // Box for user to choose to upload file from local computer
    echo '<tr><td class="descriptionbox wrap width25">';
    echo I18N::translate('Media file to upload') . '</td><td class="optionbox wrap"><input type="file" name="mediafile" onchange="updateFormat(this.value);" size="40"></td></tr>';
    // Check for thumbnail generation support
    if (WT_USER_GEDCOM_ADMIN) {
        echo '<tr><td class="descriptionbox wrap width25">';
        echo I18N::translate('Thumbnail to upload') . FunctionsPrint::i()->help_link('upload_thumbnail_file') . '</td><td class="optionbox wrap"><input type="file" name="thumbnail" size="40"></td></tr>';
    }
}

// Filename on server
$isExternal = Functions::i()->isFileExternal($gedfile);
if ($gedfile == 'FILE') {
    if (WT_USER_GEDCOM_ADMIN) {
        FunctionsEdit::i()->add_simple_tag(
            "1 $gedfile",
            '',
            I18N::translate('Filename on server'),
            I18N::translate('Do not change to keep original filename.') . '<br>' . I18N::translate('You may enter a URL, beginning with “http://”.')
        );
    }
    $fileName = '';
    $folder   = '';
} else {
    if ($isExternal) {
        $fileName = substr($gedfile, 5);
        $folder   = '';
    } else {
        $tmp      = substr($gedfile, 5);
        $fileName = basename($tmp);
        $folder   = dirname($tmp);
        if ($folder === '.') {
            $folder = '';
        }
    }

    echo '<tr>';
    echo '<td class="descriptionbox wrap width25">';
    echo I18N::translate('Filename on server'), FunctionsPrint::i()->help_link('upload_server_file');
    echo '</td>';
    echo '<td class="optionbox wrap wrap">';
    if (WT_USER_GEDCOM_ADMIN) {
        echo '<input name="filename" type="text" value="' . Filter::escapeHtml($fileName) . '" size="40"';
        if ($isExternal) {
            echo '>';
        } else {
            echo '><p class="sub">' . I18N::translate('Do not change to keep original filename.') . '</p>';
        }
    } else {
        echo $fileName;
        echo '<input name="filename" type="hidden" value="' . Filter::escapeHtml($fileName) . '" size="40">';
    }
    echo '</td>';
    echo '</tr>';
}

// Box for user to choose the folder to store the image
if (!$isExternal) {
    echo '<tr><td class="descriptionbox wrap width25">';
    echo I18N::translate('Folder name on server'), FunctionsPrint::i()->help_link('upload_server_folder'), '</td><td class="optionbox wrap">';
    //-- don’t let regular users change the location of media items
    if ($action !== 'update' || WT_USER_GEDCOM_ADMIN) {
        $mediaFolders = WT_Query_Media::folderList();
        echo '<span dir="ltr"><select name="folder_list" onchange="document.newmedia.folder.value=this.options[this.selectedIndex].value;">';
        echo '<option ';
        if ($folder == '') {
            echo 'selected';
        }
        echo ' value=""> ', I18N::translate('Choose: '), ' </option>';
        if (Auth::isAdmin()) {
            echo '<option value="other" disabled>', I18N::translate('Other folder… please type in'), "</option>";
        }
        foreach ($mediaFolders as $f) {
            echo '<option value="', $f, '" ';
            if ($folder == $f) {
                echo 'selected';
            }
            echo '>', $f, "</option>";
        }
        echo '</select></span>';
    } else {
        echo $folder;
    }
    if (Auth::isAdmin()) {
        echo '<br><span dir="ltr"><input type="text" name="folder" size="40" value="', $folder, '"></span>';
        if ($gedfile === 'FILE') {
            echo '<p class="sub">', I18N::translate('This entry is ignored if you have entered a URL into the filename field.'), '</p>';
        }
    } else {
        echo '<input name="folder" type="hidden" value="', Filter::escapeHtml($folder), '">';
    }
    echo '</td></tr>';
} else {
    echo '<input name="folder" type="hidden" value="">';
}

// 1 FILE / 2 FORM
if (preg_match('/\n(2 FORM .*)/', $gedrec, $match)) {
    $gedform = $match[1];
} else {
    $gedform = '2 FORM';
}
$formid = FunctionsEdit::i()->add_simple_tag($gedform);

// automatically set the format field from the filename
$controller->addInlineJavascript('
	function updateFormat(filename) {
		var extsearch=/\.([a-zA-Z]{3,4})$/;
		if (extsearch.exec(filename)) {
			ext = RegExp.$1.toLowerCase();
			if (ext=="jpg") ext="jpeg";
			if (ext=="tif") ext="tiff";
		} else {
			ext = "";
		}
		formfield = document.getElementById("' . $formid . '");
		formfield.value = ext;
	}
');

// 1 FILE / 2 FORM / 3 TYPE
if (preg_match('/\n(3 TYPE .*)/', $gedrec, $match)) {
    $gedtype = $match[1];
} else {
    $gedtype = '3 TYPE photo'; // default to ‘Photo’
}
FunctionsEdit::i()->add_simple_tag($gedtype);

// 1 FILE / 2 TITL
if (preg_match('/\n(2 TITL .*)/', $gedrec, $match)) {
    $gedtitl = $match[1];
} else {
    $gedtitl = '2 TITL';
}
FunctionsEdit::i()->add_simple_tag($gedtitl);

// 1 FILE / 2 TITL / 3 _HEB
if (strstr(Globals::i()->WT_TREE->getPreference('ADVANCED_NAME_FACTS'), '_HEB') !== false) {
    if (preg_match('/\n(3 _HEB .*)/', $gedrec, $match)) {
        $gedtitl = $match[1];
    } else {
        $gedtitl = '3 _HEB';
    }
    FunctionsEdit::i()->add_simple_tag($gedtitl);
}

// 1 FILE / 2 TITL / 3 ROMN
if (strstr(Globals::i()->WT_TREE->getPreference('ADVANCED_NAME_FACTS'), 'ROMN') !== false) {
    if (preg_match('/\n(3 ROMN .*)/', $gedrec, $match)) {
        $gedtitl = $match[1];
    } else {
        $gedtitl = '3 ROMN';
    }
    FunctionsEdit::i()->add_simple_tag($gedtitl);
}

// 1 _PRIM
if (preg_match('/\n(1 _PRIM .*)/', $gedrec, $match)) {
    $gedprim = $match[1];
} else {
    $gedprim = '1 _PRIM';
}
FunctionsEdit::i()->add_simple_tag($gedprim);

//-- print out editing fields for any other data in the media record
$sourceLevel = 0;
$sourceSOUR  = '';
$sourcePAGE  = '';
$sourceTEXT  = '';
$sourceDATE  = '';
$sourceQUAY  = '';
if (!empty($gedrec)) {
    preg_match_all('/\n(1 (?!FILE|FORM|TYPE|TITL|_PRIM|_THUM|CHAN|DATA).*(\n[2-9] .*)*)/', $gedrec, $matches);
    foreach ($matches[1] as $subrec) {
        $pieces = explode("\n", $subrec);
        foreach ($pieces as $piece) {
            $ft = preg_match("/(\d) (\w+)(.*)/", $piece, $match);
            if ($ft == 0) {
                continue;
            }
            $subLevel = $match[1];
            $fact     = trim($match[2]);
            $event    = trim($match[3]);
            if ($fact === 'NOTE' || $fact === 'TEXT') {
                $event .= Functions::i()->get_cont($subLevel + 1, $subrec);
            }
            if ($sourceSOUR !== '' && $subLevel <= $sourceLevel) {
                // Get rid of all saved Source data
                FunctionsEdit::i()->add_simple_tag($sourceLevel . ' SOUR ' . $sourceSOUR);
                FunctionsEdit::i()->add_simple_tag(($sourceLevel + 1) . ' PAGE ' . $sourcePAGE);
                FunctionsEdit::i()->add_simple_tag(($sourceLevel + 2) . ' TEXT ' . $sourceTEXT);
                FunctionsEdit::i()->add_simple_tag(($sourceLevel + 2) . ' DATE ' . $sourceDATE, '', WT_Gedcom_Tag::getLabel('DATA:DATE'));
                FunctionsEdit::i()->add_simple_tag(($sourceLevel + 1) . ' QUAY ' . $sourceQUAY);
                $sourceSOUR = '';
            }

            if ($fact === 'SOUR') {
                $sourceLevel = $subLevel;
                $sourceSOUR  = $event;
                $sourcePAGE  = '';
                $sourceTEXT  = '';
                $sourceDATE  = '';
                $sourceQUAY  = '';
                continue;
            }

            // Save all incoming data about this source reference
            if ($sourceSOUR !== '') {
                if ($fact === 'PAGE') {
                    $sourcePAGE = $event;
                    continue;
                }
                if ($fact === 'TEXT') {
                    $sourceTEXT = $event;
                    continue;
                }
                if ($fact === 'DATE') {
                    $sourceDATE = $event;
                    continue;
                }
                if ($fact === 'QUAY') {
                    $sourceQUAY = $event;
                    continue;
                }
                continue;
            }

            // Output anything that isn’t part of a source reference
            if (!empty($fact) && $fact !== 'CONC' && $fact !== 'CONT' && $fact !== 'DATA') {
                FunctionsEdit::i()->add_simple_tag($subLevel . ' ' . $fact . ' ' . $event);
            }
        }
    }

    if ($sourceSOUR !== '') {
        // Get rid of all saved Source data
        FunctionsEdit::i()->add_simple_tag($sourceLevel . ' SOUR ' . $sourceSOUR);
        FunctionsEdit::i()->add_simple_tag(($sourceLevel + 1) . ' PAGE ' . $sourcePAGE);
        FunctionsEdit::i()->add_simple_tag(($sourceLevel + 2) . ' TEXT ' . $sourceTEXT);
        FunctionsEdit::i()->add_simple_tag(($sourceLevel + 2) . ' DATE ' . $sourceDATE, '', WT_Gedcom_Tag::getLabel('DATA:DATE'));
        FunctionsEdit::i()->add_simple_tag(($sourceLevel + 1) . ' QUAY ' . $sourceQUAY);
    }
}
if (Auth::isAdmin()) {
    echo '<tr><td class="descriptionbox wrap width25">';
    echo WT_Gedcom_Tag::getLabel('CHAN'), '</td><td class="optionbox wrap">';
    if ($NO_UPDATE_CHAN) {
        echo '<input type="checkbox" checked name="preserve_last_changed">';
    } else {
        echo '<input type="checkbox" name="preserve_last_changed">';
    }
    echo I18N::translate('Do not update the “last change” record'), FunctionsPrint::i()->help_link('no_update_CHAN'), '<br>';
    echo '</td></tr>';
}
echo '</table>';
FunctionsEdit::i()->print_add_layer('SOUR', 1);
FunctionsEdit::i()->print_add_layer('NOTE', 1);
FunctionsEdit::i()->print_add_layer('SHARED_NOTE', 1);
FunctionsEdit::i()->print_add_layer('RESN', 1);
?>
<p id="save-cancel">
    <input type="submit" class="save" value="<?php echo I18N::translate('save'); ?>">
    <input type="button" class="cancel" value="<?php echo I18N::translate('close'); ?>" onclick="window.close();">
</p>
</form>
</div>
