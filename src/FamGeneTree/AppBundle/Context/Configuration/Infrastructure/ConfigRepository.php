<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Context\Configuration\Infrastructure;


use FamGeneTree\AppBundle\Context\Configuration\Domain\FgtConfig;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Webtrees\LegacyBundle\Legacy\Database;

class ConfigRepository implements ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    function __construct($container)
    {
        $this->container = $container;
    }

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
        $this->loadFromOldDb($config,FgtConfig::SCOPE_SITE);
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

    public function loadFromOldDb(FgtConfig $config, $scope)
    {
        if (!Database::i()->isConnected()) {
            Database::i()->createInstance(
                $this->container->getParameter('database_host'),
                $this->container->getParameter('database_port'),
                $this->container->getParameter('database_name'),
                $this->container->getParameter('database_user'),
                $this->container->getParameter('database_password'),
                $this->container->getParameter('database_prefix')
            );
        }
        $values = Database::i()->prepare(
            "SELECT SQL_CACHE setting_name, setting_value FROM `##site_setting`"
        )
                          ->fetchAssoc();
        foreach ($values as $key => $value) {
            $config->set($key, $value, $scope);
        }

    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        // TODO: Implement setContainer() method.
        $this->container = $container;
    }
}