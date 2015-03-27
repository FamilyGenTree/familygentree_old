<?php

namespace FamGenTree\SetupBundle\Context\Migration\Plan\Step\MySql;
use FamGenTree\SetupBundle\Context\Migration\Plan\Step\MigrationPlanStepPhpAbstract;

/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */


class Migration20150401_0000b extends MigrationPlanStepPhpAbstract {

    /**
     * @inheritdoc
     */
    protected function executeStep()
    {
        return true;// TODO: Implement executeStep() method.
    }

    private function step5()
    {
////////////////////////////////////////////////////////////////////////////////
// Step five - site setup data
////////////////////////////////////////////////////////////////////////////////

        if (!isset($_POST['wtname'])) {
            $_POST['wtname'] = '';
        }
        if (!isset($_POST['wtuser'])) {
            $_POST['wtuser'] = '';
        }
        if (!isset($_POST['wtpass'])) {
            $_POST['wtpass'] = '';
        }
        if (!isset($_POST['wtpass2'])) {
            $_POST['wtpass2'] = '';
        }
        if (!isset($_POST['wtemail'])) {
            $_POST['wtemail'] = '';
        }

        if (empty($_POST['wtname']) || empty($_POST['wtuser']) || strlen($_POST['wtpass']) < 6 || strlen($_POST['wtpass2']) < 6 || empty($_POST['wtemail']) || $_POST['wtpass'] <> $_POST['wtpass2']) {
            if (strlen($_POST['wtpass']) > 0 && strlen($_POST['wtpass']) < 6) {
                echo '<p class="bad">', I18N::translate('The password needs to be at least six characters long.'), '</p>';
            } elseif ($_POST['wtpass'] <> $_POST['wtpass2']) {
                echo '<p class="bad">', I18N::translate('The passwords do not match.'), '</p>';
            } elseif ((empty($_POST['wtname']) || empty($_POST['wtuser']) || empty($_POST['wtpass']) || empty($_POST['wtemail'])) && $_POST['wtname'] . $_POST['wtuser'] . $_POST['wtpass'] . $_POST['wtemail'] != '') {
                echo '<p class="bad">', I18N::translate('You must enter all the administrator account fields.'), '</p>';
            }
            echo
            '<h2>', I18N::translate('System settings'), '</h2>',
            '<h3>', I18N::translate('Administrator account'), '</h3>',
            '<p>', I18N::translate('You need to set up an administrator account.  This account can control all aspects of this webtrees installation.  Please choose a strong password.'), '</p>',
            '<fieldset><legend>', I18N::translate('Administrator account'), '</legend>',
            '<table border="0"><tr><td>',
            I18N::translate('Your name'), '</td><td>',
            '<input type="text" name="wtname" value="', Filter::escapeHtml($_POST['wtname']), '" autofocus></td><td>',
            I18N::translate('This is your real name, as you would like it displayed on screen.'),
            '</td></tr><tr><td>',
            I18N::translate('Login ID'), '</td><td>',
            '<input type="text" name="wtuser" value="', Filter::escapeHtml($_POST['wtuser']), '"></td><td>',
            I18N::translate('You will use this to login to webtrees.'),
            '</td></tr><tr><td>',
            I18N::translate('Password'), '</td><td>',
            '<input type="password" name="wtpass" value="', Filter::escapeHtml($_POST['wtpass']), '"></td><td>',
            I18N::translate('This must to be at least six characters.  It is case-sensitive.'),
            '</td></tr><tr><td>',
            '&nbsp;', '</td><td>',
            '<input type="password" name="wtpass2" value="', Filter::escapeHtml($_POST['wtpass2']), '"></td><td>',
            I18N::translate('Type your password again, to make sure you have typed it correctly.'),
            '</td></tr><tr><td>',
            I18N::translate('Email address'), '</td><td>',
            '<input type="email" name="wtemail" value="', Filter::escapeHtml($_POST['wtemail']), '"></td><td>',
            I18N::translate('This email address will be used to send password reminders, website notifications, and messages from other family members who are registered on the website.'),
            '</td></tr><tr><td>',
            '</td></tr></table>',
            '</fieldset>',
            '<br><hr><input type="submit" id="btncontinue" value="', I18N::translate('continue'), '">',
            '</form>',
            '</body></html>';

            return;
        } else {
            // Copy these values through to the next step
            echo '<input type="hidden" name="wtname"     value="', Filter::escapeHtml($_POST['wtname']), '">';
            echo '<input type="hidden" name="wtuser"     value="', Filter::escapeHtml($_POST['wtuser']), '">';
            echo '<input type="hidden" name="wtpass"     value="', Filter::escapeHtml($_POST['wtpass']), '">';
            echo '<input type="hidden" name="wtpass2"    value="', Filter::escapeHtml($_POST['wtpass2']), '">';
            echo '<input type="hidden" name="wtemail"    value="', Filter::escapeHtml($_POST['wtemail']), '">';
        }
    }
}