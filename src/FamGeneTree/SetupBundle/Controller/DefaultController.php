<?php

namespace FamGeneTree\SetupBundle\Controller;

use FamGeneTree\SetupBundle\Context\Setup\Config\SetupConfig;
use FamGeneTree\SetupBundle\Context\Setup\Step\PreRequirementsStep;
use FamGeneTree\SetupBundle\Context\Setup\Step\StepResult;
use FamGeneTree\SetupBundle\Form\DatabaseConnectionForm;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
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

        /** @var PreRequirementsStep $servicePreRequirements */
        $servicePreRequirements = $this->get('fgt.setup.prerequirementsfactory');

        $servicePreRequirements->run();
        $results = $servicePreRequirements->getCheckResults();
        $overall = $servicePreRequirements->getOverall();

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
            $dbStep      = $manager->getStepServiceDatabase();
            $checkResult = $dbStep->checkConfig($manager->getConfigDatabase());
            if ($checkResult->isSuccess()) {
                $manager->setStepCompleted(SetupConfig::STEP_DATABASE_CREDENTIALS);
                $isMigrationNeeded    = $dbStep->isMigrationNeeded($manager->getConfigDatabase());
                $isConfirmedMigration = $form->get('confirmedMigration')->getData();
                $canContinue          = $isMigrationNeeded ? $isConfirmedMigration : true;
                if ($isMigrationNeeded && !$isConfirmedMigration) {
                    $form->addError(new FormError('Migration is needed and you need to confirm. Please confirm.'));
                }
            } else {
                //failed
                /** @var StepResult $result */
                foreach ($checkResult->getResults() as $result) {
                    if (!$result->isSuccess()) {
                        $form->addError(new FormError($result->getMessage()));
                    }
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
                    'continue_button' => 'enabled'
                )
            )
        );
    }

    public function execDatabaseCreateAction(Request $request)
    {
        $manager = $this->get('fgt.setup.manager');
        $manager->setCurrentStep(SetupConfig::STEP_DATABASE_RUN_MIGRATIONS);
        if ($request->request->has('action_continue')) {
            return $this->redirect($this->generateUrl($manager->getRouteToStep($manager->getNextStep())));
        }

        $results = null;
        try {
            $stepDatabase = $this->container->get('fgt.setup.service.step.database.creation');
            $stepDatabase->setDatabaseConfig($manager->getConfigDatabase());
            $stepDatabase->run();
            $results = $stepDatabase->getResults();
            $manager->setCurrentStepCompleted();
            $manager->writeConfig();
        } catch (\Exception $ex) {
            $results[] = new StepResult(
                'Database Creation',
                StepResult::STATE_FAILED,
                'Something went wrong: ' . $ex
            );
        }

        return $this->render(
            'FamGeneTreeSetupBundle:Default:database-results.html.twig',
            array_merge(
                $this->getCommonValues(),
                array(
                    'name'            => 'Database Creation',
                    'results'         => $results,
                    'form'            => $this->createFormBuilder()->getForm()->createView(),
                    'continue_button' => 'enabled',
                    'previous_button' => 'disabled'

                )
            )
        );
    }

    public function firstUserSettingsAction(Request $request)
    {
        $manager = $this->get('fgt.setup.manager');
        $manager->setCurrentStep(SetupConfig::STEP_FIRST_USER);
        $canContinue = false;

        $form = $this->createForm(new DatabaseConnectionForm(), $manager->getConfigDatabase());;
        $form->handleRequest($request);

        if ($form->isValid()) {
            $firstUserStep = $manager->getStepServiceFirstUser();
            $checkResult   = $firstUserStep->checkConfig($manager->getConfigFirstUser());
            if ($checkResult->isSuccess()) {
                try {
                    $firstUserStep->run();
                    $manager->setStepCompleted(SetupConfig::STEP_FIRST_USER);
                    $canContinue = true;
                } catch (\Exception $ex) {
                    $form->addError(new FormError('Migration is needed and you need to confirm. Please confirm.'));
                }
            } else {
                //failed
                /** @var StepResult $result */
                foreach ($checkResult->getResults() as $result) {
                    if (!$result->isSuccess()) {
                        $form->addError(new FormError($result->getMessage()));
                    }
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
            'FamGeneTreeSetupBundle:Default:first-user-settings.html.twig',
            array_merge(
                $this->getCommonValues(),
                array(
                    'name'            => 'First User',
                    'form'            => $form->createView(),
                    'continue_button' => 'enabled',
                    'previous_button' => 'disabled'

                )
            )
        );
    }

    public function finishedAction()
    {
        return $this->render(
            'FamGeneTreeSetupBundle:Default:finished.html.twig',
            array_merge(
                $this->getCommonValues(),
                array(
                    'name'            => 'Setup Finished',
                    'form'            => $this->createFormBuilder()->getForm()->createView(),
                    'continue_button' => 'enabled',
                    'previous_button' => 'disabled'

                )
            )
        );
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
    }

}
