<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Context\Configuration\Domain;


class FgtConfig implements ConfigKeys
{
    const SCOPE_SITE    = 0;
    const SCOPE_USER    = 1;
    const SCOPE_TREE    = 2;
    const SCOPE_THEME   = 5;
    const SCOPE_RUNTIME = 10;

    protected static $SCOPE_ORDER = array(
        self::SCOPE_RUNTIME,
        self::SCOPE_TREE,
        self::SCOPE_USER,
        self::SCOPE_THEME,
        self::SCOPE_SITE,
    );

    /**
     * @var ConfigSectionSite
     */
    protected $configScopeSite = null;
    /**
     * @var ConfigSectionUser
     */
    protected $configScopeUser;
    /**
     * @var ConfigSectionTree
     */
    protected $configScopeTree;
    /**
     * @var ConfigSectionRuntime
     */
    protected $configScopeRuntime;
    /**
     * @var ConfigSectionTheme
     */
    protected $configScopeTheme;

    function __construct()
    {
        $this->configScopeSite    = new ConfigSectionSite();
        $this->configScopeUser    = new ConfigSectionUser();
        $this->configScopeTree    = new ConfigSectionTree();
        $this->configScopeRuntime = new ConfigSectionRuntime();
        $this->configScopeTheme   = new ConfigSectionTheme();
    }

    /**
     * @param      $key
     * @param      $value
     * @param      $scope
     * @param bool $immutable
     *
     * @return $this Fluent interface
     * @throws \FamGeneTree\AppBundle\Context\Configuration\Domain\Exception\ImmutableValue
     */
    public function set($key, $value, $scope, $immutable = false)
    {
        $this->validateScope($scope);
        $section = $this->getSectionForSection($scope);
        $section->setValue($key, $value, $immutable);

        return $this;
    }

    /**
     * @param     $key
     * @param int $scope
     *
     * @return ConfigValue|null
     */
    public function get($key, $scope = null)
    {
        $this->validateScope($scope);

        if (null === $scope) {
            foreach (static::$SCOPE_ORDER as $nextScope) {
                $section = $this->getSectionForSection($nextScope);
                if ($section->isDefined($key)) {
                    return $section->getValue($key);
                }
            }
        } else {
            $section = $this->getSectionForSection($scope);

            return $section->getValue($key);
        }
    }

    public function getValue($key, $scope = null, $default = null)
    {
        $ret = $this->get($key, $scope);

        return $ret != null ? $ret->getValue() : $default;
    }

    public function getConfigTheme($key)
    {
        return $this->getSectionForSection(static::SCOPE_THEME)->getValue($key);
    }

    protected function validateScope($scope)
    {
        return in_array($scope, static::$SCOPE_ORDER);
    }

    /**
     * @param $scope
     *
     * @return ConfigSection|null
     */
    protected function getSectionForSection($scope)
    {
        switch ($scope) {
            case static::SCOPE_SITE:
                return $this->configScopeSite;
            case static::SCOPE_USER:
                return $this->configScopeUser;
            case static::SCOPE_TREE:
                return $this->configScopeTree;
            case static::SCOPE_RUNTIME:
                return $this->configScopeRuntime;
            case static::SCOPE_THEME:
                return $this->configScopeTheme;
        }

        return null;
    }
}