<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
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
            new FOS\UserBundle\FOSUserBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new FamGeneTree\AppBundle\FamGeneTreeAppBundle(),
            new Webtrees\LegacyBundle\WebtreesLegacyBundle(),
            new Webtrees\LegacyThemeBundle\WebtreesLegacyThemeBundle(),
            new Webtrees\LegacyAdminThemeBundle\WebtreesLegacyAdminThemeBundle()
        );

        if (false === $this->isSetupMode()) {
        } else {
            $bundles[] = new FamGeneTree\SetupBundle\FamGeneTreeSetupBundle();
        }


        if (in_array(
            $this->getEnvironment(),
            array(
                'dev',
                'test'
            ))
        ) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if ($this->isSetupMode()) {
            $loader->load(__DIR__ . '/config/config_setup.yml');
        } else {
            $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
        }
    }

    protected function isSetupMode()
    {
        if ($this->getEnvironment() === 'test') {
            return false;
        }
        if (!file_exists(__DIR__ . '/config/parameters.yml')) {
            return true;
        }

        if (strpos(file_get_contents(__DIR__ . '/config/parameters.yml'), 'ThisTokenIsNotSoSecretChangeIt') !== false) {
            return true;
        }

        return false;
    }
}
