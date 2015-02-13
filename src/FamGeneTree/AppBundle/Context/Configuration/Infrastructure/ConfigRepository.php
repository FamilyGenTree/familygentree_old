<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Context\Configuration\Infrastructure;


use FamGeneTree\AppBundle\Context\Configuration\Domain\FgtConfig;

class ConfigRepository
{

    /**
     * @return FgtConfig
     */
    public function load()
    {
        echo __METHOD__;
        $config = new FgtConfig();
        $values = parse_ini_file(__DIR__ . '/../../../Resources/config/config.system.ini');
        foreach ($values as $key => $value) {
            $config->set($key, $value, FgtConfig::SCOPE_SITE);
        }
        $values = parse_ini_file(__DIR__ . '/../../../Resources/config/config.site.ini');
        foreach ($values as $key => $value) {
            $config->set($key, $value, FgtConfig::SCOPE_SITE);
        }
        if (file_exists(__DIR__ . '/../../../Resources/config/config.user.ini')) {
            $values = parse_ini_file(__DIR__ . '/../../../Resources/config/config.user.ini');
            foreach ($values as $key => $value) {
                $config->set($key, $value, FgtConfig::SCOPE_THEME);
            }
        }
        if (file_exists(__DIR__ . '/../../../Resources/config/config.theme.ini')) {
            $values = parse_ini_file(__DIR__ . '/../../../Resources/config/config.theme.ini');
            foreach ($values as $key => $value) {
                $config->set($key, $value, FgtConfig::SCOPE_THEME);
            }
        }

        return $config;
    }

    public function loadInitialConfig()
    {

    }

    public function store(FgtConfig $configuration)
    {

    }
}