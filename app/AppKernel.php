<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [];

        if ('test' === $this->getEnvironment() || $this->isSetupMode()) {
            $bundles[] = new FamGeneTree\SetupBundle\FamGeneTreeSetupBundle();
        }

        if (in_array(
            $this->getEnvironment(),
            array(
                'dev',
                'test',
                'setup'
            ))
        ) {
            $bundles = array_merge(
                $bundles,
                $this->getDevBundles()
            );
        }

        if (false === $this->isSetupMode()) {
            $bundles = array_merge(
                $this->getWebUIBundles(),
                $bundles
            );
        }

        $bundles = array_merge(
            $this->getCoreBundles(),
            $bundles
        );

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    protected function isSetupMode()
    {
        if ($this->getEnvironment() === 'test') {
            return false;
        }
        if ($this->getEnvironment() === 'setup') {
            return true;
        }

        return false === file_exists(__DIR__ . '/config/parameters.yml');
    }

    /**
     * @return array
     */
    protected function getCoreBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new FamGenTree\Theme\MainBundle\FamGenTreeThemeMainBundle(),
        );

        return $bundles;
    }

    /**
     *
     * @return array
     */
    protected function getWebUIBundles()
    {
        return [
            new FOS\UserBundle\FOSUserBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FamGeneTree\AppBundle\FamGeneTreeAppBundle(),
            new Webtrees\LegacyBundle\WebtreesLegacyBundle(),
            new Webtrees\LegacyThemeBundle\WebtreesLegacyThemeBundle(),
            new Webtrees\LegacyAdminThemeBundle\WebtreesLegacyAdminThemeBundle()
        ];
    }

    /**
     *
     * @return array
     */
    protected function getDevBundles()
    {
        return [
            new Symfony\Bundle\DebugBundle\DebugBundle(),
            new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),
            new Sensio\Bundle\DistributionBundle\SensioDistributionBundle(),
            new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle()
        ];
    }
}
