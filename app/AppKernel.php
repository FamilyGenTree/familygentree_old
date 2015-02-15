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
            new FamGeneTree\SetupBundle\FamGeneTreeSetupBundle()
        );

        if ($this->isSetup()) {
            $bundles[] = new FOS\UserBundle\FOSUserBundle();
            $bundles[] = new Knp\Bundle\MenuBundle\KnpMenuBundle();
            $bundles[] = new Webtrees\LegacyBundle\WebtreesLegacyBundle();
            $bundles[] = new FamGeneTree\AppBundle\FamGeneTreeAppBundle();
            $bundles[] = new Webtrees\LegacyThemeBundle\WebtreesLegacyThemeBundle();
            $bundles[] = new Webtrees\LegacyAdminThemeBundle\WebtreesLegacyAdminThemeBundle();
        } else {
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
        $loader->load(__DIR__ . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    protected function isSetup()
    {
        return file_exists(__DIR__ . '/config/parameters.yml');
    }
}
