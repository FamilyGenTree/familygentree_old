<?php
namespace FamGeneTree\SetupBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FamGeneTree\AppBundle\Context\Configuration\Domain\ConfigKeys;
use FamGeneTree\SetupBundle\Context\Setup\Config\SetupConfig;

/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

abstract class AbstractController extends Controller {

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