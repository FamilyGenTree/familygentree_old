<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            0 => new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            1 => new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            2 => new Symfony\Bundle\TwigBundle\TwigBundle(),
            3 => new Symfony\Bundle\MonologBundle\MonologBundle(),
            4 => new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            5 => new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            6 => new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            7 => new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            8 => new FamGenTree\Theme\MainBundle\FamGenTreeThemeMainBundle(),
        );

        if (in_array($this->getEnvironment(), [
                'setup'
            ])
            || true === $this->isSetupMode()
        ) {
            $bundles[] = new FamGeneTree\SetupBundle\FamGeneTreeSetupBundle();
        } else {
            if ('test' === $this->getEnvironment()) {
                $bundles[] = new FamGeneTree\SetupBundle\FamGeneTreeSetupBundle();
            }
            $bundles[] = new FOS\UserBundle\FOSUserBundle();
            $bundles[] = new Knp\Bundle\MenuBundle\KnpMenuBundle();
            $bundles[] = new FamGeneTree\AppBundle\FamGeneTreeAppBundle();
            $bundles[] = new Webtrees\LegacyBundle\WebtreesLegacyBundle();
            $bundles[] = new Webtrees\LegacyThemeBundle\WebtreesLegacyThemeBundle();
            $bundles[] = new Webtrees\LegacyAdminThemeBundle\WebtreesLegacyAdminThemeBundle();
        }
        if (in_array(
            $this->getEnvironment(),
            array(
                'dev',
                'test',
                'setup'
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

    protected function isSetupMode()
    {
        if ($this->getEnvironment() === 'test') {
            return false;
        }
        if ($this->getEnvironment() === 'setup') {
            return true;
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
