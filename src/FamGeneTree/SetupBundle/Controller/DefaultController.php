<?php

namespace FamGeneTree\SetupBundle\Controller;

use FamGeneTree\AppBundle\Context\Configuration\Domain\ConfigKeys;
use FamGeneTree\SetupBundle\Context\Setup\Config\SetupConfig;
use FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\CheckInterface;
use FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementsFactory;
use FamGeneTree\SetupBundle\Context\Setup\Step\StepResult;
use FamGeneTree\SetupBundle\Form\DatabaseConnectionForm;
use Fgt\Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $manager = $this->get('fgt.setup.manager');
        if ($manager->getCurrentStep() === null) {
            $manager->setCurrentStep($manager->getFirstIncompleteStep());
        }

        return $this->redirect($this->generateUrl($manager->getRouteToStep($manager->getCurrentStep())));
    }

    public function stepLocaleAction(Request $request)
    {
        $setupConfig = $this->getSetupConfig($request, true);
        $manager     = $this->get('fgt.setup.manager');

        $form = $this->createFormBuilder($setupConfig)
                     ->add('setupLocale', 'locale')
                     ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $localeName = $form->get('setupLocale')->getData();
            $setupConfig->setSetupLocale($localeName);
            $setupConfig->setStepCompleted(SetupConfig::STEP_LOCALE);

            return $this->redirect($this->generateUrl($manager->getRouteToStep($manager->getNextStep())));
        }

        return $this->render(
            'FamGeneTreeSetupBundle:Default:choose-language.html.twig',
            array_merge(
                $this->getCommonValues(),
                array(
                    'name'        => 'Welcome',
                    'form'        => $form->createView(),
                    'back_button' => false
                )
            )
        );
    }

    public function restartAction(Request $request)
    {
        $request->getSession()->clear();

        return $this->redirect($this->generateUrl('fgt_setup_root'));
    }

    /**
     * Step 1: check pre requirements
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function checkPreRequirementsAction(Request $request)
    {
        $manager = $this->get('fgt.setup.manager');
        $manager->setCurrentStep(SetupConfig::STEP_PRE_REQUIREMENTS);
        if ($request->request->has('action_continue')) {
            return $this->redirect($this->generateUrl($manager->getRouteToStep($manager->getNextStep())));
        }
        if ($request->request->has('action_previous')) {
            return $this->redirect($this->generateUrl($manager->getRouteToStep($manager->getPreviousStep())));
        }

        /** @var PreRequirementsFactory $servicePreRequirements */
        $servicePreRequirements = $this->get('fgt.setup.prerequirementsfactory');

        $results = array();
        $overall = true;
        /** @var CheckInterface $check */
        foreach ($servicePreRequirements->getChecks() as $check) {
            $check->run();
            $results[] = array(
                'name'        => $check->getName(),
                'description' => $check->getDescription(),
                'results'     => $check->getResults()
            );
            $overall   = $overall && $check->isPassed();
        }

        return $this->render(
            'FamGeneTreeSetupBundle:Default:prerequirements.html.twig',
            array_merge(
                $this->getCommonValues(),
                array(
                    'name'            => 'Pre Requirements',
                    'results'         => $results,
                    'form'            => $this->createFormBuilder()->getForm()->createView(),
                    'continue_button' => $overall ? 'enabled' : 'disabled',
                )
            )
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getDatabaseSettingAction(Request $request)
    {
        $manager = $this->get('fgt.setup.manager');
        $manager->setCurrentStep(SetupConfig::STEP_DATABASE_CREDENTIALS);
        $canContinue = false;

        $form = $this->createForm(new DatabaseConnectionForm(), $manager->getConfigDatabase());
        $form->handleRequest($request);


        if ($form->isValid()) {
            $dbStep      = $manager->getStepDatabase();
            $checkResult = $dbStep->checkConfig($manager->getConfigDatabase());
            if ($checkResult->isSuccess()) {
                $canContinue = true;
            } else {
                //failed
                /** @var StepResult $result */
                foreach ($checkResult->getResults() as $result) {
                    $form->addError(new FormError($result->getMessage()));
                }
            }
        }
        if ($canContinue && $request->request->has('action_continue')) {
            return $this->redirect($this->generateUrl($manager->getRouteToStep($manager->getNextStep())));
        }
        if ($request->request->has('action_previous')) {
            return $this->redirect($this->generateUrl($manager->getRouteToStep($manager->getPreviousStep())));
        }

        return $this->render(
            'FamGeneTreeSetupBundle:Default:database-settings.html.twig',
            array_merge(
                $this->getCommonValues(),
                array(
                    'name'            => 'Database Settings',

                    'form'            => $form->createView(),
                    'continue_button' => 'enabled',
                )
            )
        );
    }

    public function finishedAction()
    {
    }

    public function step3()
    {
        ////////////////////////////////////////////////////////////////////////////////
// Step three - Database connection.
////////////////////////////////////////////////////////////////////////////////

        if (!isset($_POST['dbhost'])) {
            $_POST['dbhost'] = 'localhost';
        }
        if (!isset($_POST['dbport'])) {
            $_POST['dbport'] = '3306';
        }
        if (!isset($_POST['dbuser'])) {
            $_POST['dbuser'] = '';
        }
        if (!isset($_POST['dbpass'])) {
            $_POST['dbpass'] = '';
        }
        if (!isset($_POST['dbname'])) {
            $_POST['dbname'] = '';
        }
        if (!isset($_POST['tblpfx'])) {
            $_POST['tblpfx'] = 'wt_';
        }

        try {
            $db_version_ok = false;
            Database::i()->createInstance(
                $_POST['dbhost'],
                $_POST['dbport'],
                '', // No DBNAME - we will connect to it explicitly
                $_POST['dbuser'],
                $_POST['dbpass'],
                $_POST['tblpfx']
            );
            Database::i()->exec("SET NAMES 'utf8'");
            $row = Database::i()->prepare("SHOW VARIABLES LIKE 'VERSION'")
                           ->fetchOneRow();
            if (version_compare($row->value, WT_REQUIRED_MYSQL_VERSION, '<')) {
                echo '<p class="bad">', I18N::translate('This database is only running MySQL version %s.  You cannot install webtrees here.', $row->value), '</p>';
            } else {
                $db_version_ok = true;
            }
        } catch (PDOException $ex) {
            Database::i()->disconnect();
            if ($_POST['dbuser']) {
                // If we’ve supplied a login, then show the error
                echo
                '<p class="bad">', I18N::translate('Unable to connect using these settings.  Your server gave the following error.'), '</p>',
                '<pre>', $ex->getMessage(), '</pre>',
                '<p class="bad">', I18N::translate('Check the settings and try again.'), '</p>';
            }
        }

        if (empty($_POST['dbuser']) || !Database::i()->isConnected() || !$db_version_ok) {
            echo
            '<h2>', I18N::translate('Connection to database server'), '</h2>',
            '<p>', I18N::translate('webtrees needs a MySQL database, version %s or later.', WT_REQUIRED_MYSQL_VERSION), '</p>',
            '<p>', I18N::translate('Your server’s administrator will provide you with the connection details.'), '</p>',
            '<fieldset><legend>', I18N::translate('Database connection'), '</legend>',
            '<table border="0"><tr><td>',
            I18N::translate('Server name'), '</td><td>',
            '<input type="text" name="dbhost" value="', Filter::escapeHtml($_POST['dbhost']), '" dir="ltr"></td><td>',
            I18N::translate('Most sites are configured to use localhost.  This means that your database runs on the same computer as your web server.'),
            '</td></tr><tr><td>',
            I18N::translate('Port number'), '</td><td>',
            '<input type="text" name="dbport" value="', Filter::escapeHtml($_POST['dbport']), '"></td><td>',
            I18N::translate('Most sites are configured to use the default value of 3306.'),
            '</td></tr><tr><td>',
            I18N::translate('Database user account'), '</td><td>',
            '<input type="text" name="dbuser" value="', Filter::escapeHtml($_POST['dbuser']), '" autofocus></td><td>',
            I18N::translate('This is case sensitive.'),
            '</td></tr><tr><td>',
            I18N::translate('Database password'), '</td><td>',
            '<input type="password" name="dbpass" value="', Filter::escapeHtml($_POST['dbpass']), '"></td><td>',
            I18N::translate('This is case sensitive.'),
            '</td></tr><tr><td>',
            '</td></tr></table>',
            '</fieldset>',
            '<br><hr><input type="submit" id="btncontinue" value="', I18N::translate('continue'), '">',
            '</form>',
            '</body></html>';

            return;
        } else {
            // Copy these values through to the next step
            echo '<input type="hidden" name="dbhost" value="', Filter::escapeHtml($_POST['dbhost']), '">';
            echo '<input type="hidden" name="dbport" value="', Filter::escapeHtml($_POST['dbport']), '">';
            echo '<input type="hidden" name="dbuser" value="', Filter::escapeHtml($_POST['dbuser']), '">';
            echo '<input type="hidden" name="dbpass" value="', Filter::escapeHtml($_POST['dbpass']), '">';
        }
    }

    public function step4()
    {
        ////////////////////////////////////////////////////////////////////////////////
// Step four - Database connection.
////////////////////////////////////////////////////////////////////////////////

// The character ` is not valid in database or table names (even if escaped).
// By removing it, we can ensure that our SQL statements are quoted correctly.
//
// Other characters may be invalid (objects must be valid filenames on the
// MySQL server’s filesystem), so block the usual ones.
        $DBNAME    = str_replace(array(
                                     '`',
                                     '"',
                                     '\'',
                                     ':',
                                     '/',
                                     '\\',
                                     '\r',
                                     '\n',
                                     '\t',
                                     '\0'
                                 ), '', $_POST['dbname']);
        $TBLPREFIX = str_replace(array(
                                     '`',
                                     '"',
                                     '\'',
                                     ':',
                                     '/',
                                     '\\',
                                     '\r',
                                     '\n',
                                     '\t',
                                     '\0'
                                 ), '', $_POST['tblpfx']);

// If we have specified a database, and we have not used invalid characters,
// try to connect to it.
        $dbname_ok = false;
        if ($DBNAME && $DBNAME == $_POST['dbname'] && $TBLPREFIX == $_POST['tblpfx']) {
            try {
                // Try to create the database, if it does not exist.
                Database::i()->exec("CREATE DATABASE IF NOT EXISTS `{$DBNAME}` COLLATE utf8_unicode_ci");
            } catch (PDOException $ex) {
                // If we have no permission to do this, there’s nothing helpful we can say.
                // We’ll get a more helpful error message from the next test.
            }
            try {
                Database::i()->exec("USE `{$DBNAME}`");
                $dbname_ok = true;
            } catch (PDOException $ex) {
                echo
                '<p class="bad">', I18N::translate('Unable to connect using these settings.  Your server gave the following error.'), '</p>',
                '<pre>', $ex->getMessage(), '</pre>',
                '<p class="bad">', I18N::translate('Check the settings and try again.'), '</p>';
            }
        }

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
        if ($dbname_ok) {
            try {
                // PhpGedView (4.2.4 and later) has a site_setting.site_setting_name column.
                // [We changed the column name in webtrees, so we can tell the difference!]
                $dummy = Database::i()
                                 ->prepare("SELECT site_setting_value FROM `##site_setting` WHERE site_setting_name='PGV_SCHEMA_VERSION'")
                                 ->fetchOne();
                echo '<p class="bad">', I18N::translate('This database and table-prefix appear to be used by another application.  If you have an existing PhpGedView system, you should create a new webtrees system.  You can import your PhpGedView data and settings later.'), '</p>';
                $dbname_ok = false;
            } catch (PDOException $ex) {
                // Table/column not found?  Good!
            }
        }

        if (!$dbname_ok) {
            echo
            '<h2>', I18N::translate('Database and table names'), '</h2>',
            '<p>', I18N::translate('A database server can store many separate databases.  You need to select an existing database (created by your server’s administrator) or create a new one (if your database user account has sufficient privileges).'), '</p>',
            '<fieldset><legend>', I18N::translate('Database name'), '</legend>',
            '<table border="0"><tr><td>',
            I18N::translate('Database name'), '</td><td>',
            '<input type="text" name="dbname" value="', Filter::escapeHtml($_POST['dbname']), '" autofocus></td><td>',
            I18N::translate('This is case sensitive.  If a database with this name does not already exist webtrees will attempt to create one for you.  Success will depend on permissions set for your web server, but you will be notified if this fails.'),
            '</td></tr><tr><td>',
            I18N::translate('Table prefix'), '</td><td>',
            '<input type="text" name="tblpfx" value="', Filter::escapeHtml($_POST['tblpfx']), '"></td><td>',
            I18N::translate('The prefix is optional, but recommended.  By giving the table names a unique prefix you can let several different applications share the same database.  “wt_” is suggested, but can be anything you want.'),
            '</td></tr></table>',
            '</fieldset>',
            '<br><hr><input type="submit" id="btncontinue" value="', I18N::translate('continue'), '">',
            '</form>',
            '</body></html>';

            return;
        } else {
            // Copy these values through to the next step
            echo '<input type="hidden" name="dbname" value="', Filter::escapeHtml($_POST['dbname']), '">';
            echo '<input type="hidden" name="tblpfx" value="', Filter::escapeHtml($_POST['tblpfx']), '">';
        }
    }

    public function step5()
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

    public function step6()
    {

        // Write the config file.  We already checked that this would work.
        $config_ini_php =
            '; <' . '?php exit; ?' . '> DO NOT DELETE THIS LINE' . PHP_EOL .
            'dbhost="' . addcslashes($_POST['dbhost'], '"') . '"' . PHP_EOL .
            'dbport="' . addcslashes($_POST['dbport'], '"') . '"' . PHP_EOL .
            'dbuser="' . addcslashes($_POST['dbuser'], '"') . '"' . PHP_EOL .
            'dbpass="' . addcslashes($_POST['dbpass'], '"') . '"' . PHP_EOL .
            'dbname="' . addcslashes($_POST['dbname'], '"') . '"' . PHP_EOL .
            'tblpfx="' . addcslashes($_POST['tblpfx'], '"') . '"' . PHP_EOL;

        file_put_contents(Config::get(Config::DATA_DIRECTORY) . 'config.ini.php', $config_ini_php);

        // Done - start using webtrees
        echo
        '<script>document.location=document.location;</script>',
        '</form></body></html>';

        return;
        //} catch (\PDOException $ex) {
        //    echo
        //    '<p class="bad">', I18N::translate('An unexpected database error occurred.'), '</p>',
        //    '<pre>', $ex->getMessage(), '</pre>',
        //    '<p class="info">', I18N::translate('The webtrees developers would be very interested to learn about this error.  If you contact them, they will help you resolve the problem.'), '</p>';
        //}
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return SetupConfig
     */
    protected function getSetupConfig(Request $request, $createIfAbsent = false)
    {
        $setupConfig = $request->getSession()->get('setup-config');
        if ($createIfAbsent && null === $setupConfig) {
            $setupConfig = new SetupConfig();
            $request->getSession()->set('setup-config', $setupConfig);
        }

        return $setupConfig;
    }

    protected function getCommonValues()
    {
        $fgtConfig = $this->get('fgt.configuration.setup');

        return array(
            'system'          => array(
                'name' => $fgtConfig->get(ConfigKeys::SYSTEM_NAME)
            ),
            'back_button'     => true,
            'continue_button' => true
        );
    }

    protected function saveSetupConfig(Request $request, SetupConfig $setupConfig)
    {
        $request->getSession()->set('setup-config', $setupConfig);
    }
}
