<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGenTree\SetupBundle\Context\Migration\Service;


class MigrateVersion {

    public function isOtherSystemDb() {
        return false;
    }

    public function isMigrationPossible() {

    }

    public function getInstalledVersion() {

    }

    protected function checkPhpGedViewBefore4_2_4() {

    /*
// If the database exists, check whether it is already used by another application.
if ($dbname_ok) {
try {
    // PhpGedView (4.2.3 and earlier) and many other applications have a USERS table.
    // webtrees has a USER table
$dummy = Database::i()->prepare("SELECT COUNT(*) FROM `##users`")
->fetchOne();
echo '<p class="bad">', I18N::translate('This database and table-prefix appear to be used by another application.  If you have an existing PhpGedView system, you should create a new webtrees system.  You can import your PhpGedView data and settings later.'), '</p>';
$dbname_ok = false;
} catch (PDOException $ex) {
    // Table not found?  Good!
}
        }

*/
    }

    protected function checkPhpGedViewAfterOr4_2_4() {
//if ($dbname_ok) {
//try {
//    // PhpGedView (4.2.4 and later) has a site_setting.site_setting_name column.
//    // [We changed the column name in webtrees, so we can tell the difference!]
//$dummy = Database::i()
//->prepare("SELECT site_setting_value FROM `##site_setting` WHERE site_setting_name='PGV_SCHEMA_VERSION'")
//->fetchOne();
//echo '<p class="bad">', I18N::translate('This database and table-prefix appear to be used by another application.  If you have an existing PhpGedView system, you should create a new webtrees system.  You can import your PhpGedView data and settings later.'), '</p>';
//$dbname_ok = false;
//} catch (PDOException $ex) {
//    // Table/column not found?  Good!
//}
        }

}