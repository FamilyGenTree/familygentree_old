<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\AppBundle\Context\Configuration\Infrastructure;

use FamGeneTree\AppBundle\Context\Configuration\Domain\SymfonyParameters\ParametersRepositoryInterface;
use FamGeneTree\AppBundle\Context\Configuration\Domain\SymfonyParameters\SymfonyParameters;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class ParametersRepository
    extends ContainerAware
    implements ParametersRepositoryInterface
{

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return SymfonyParameters
     */
    public function loadParametersTemplate()
    {
        // TODO: Implement loadParametersTemplate() method.
        $param         = Yaml::parse($this->getParametersPath() . '.dist', true);
        $symfonyParams = new SymfonyParameters();
        if (isset($param['parameters'])) {
            $symfonyParams->mergeParams($param['parameters']);
        }

        return $symfonyParams;
    }

    /**
     * @return SymfonyParameters
     */
    public function loadParameters()
    {
        if (!file_exists($this->getParametersPath())) {
            return $this->loadParametersTemplate();
        }
        $param         = Yaml::parse($this->getParametersPath(), true);
        $symfonyParams = new SymfonyParameters();
        if (isset($param['parameters'])) {
            $symfonyParams->mergeParams($param['parameters']);
        }

        return $symfonyParams;
    }

    public function writeParameters(SymfonyParameters $symfonyParameters)
    {
        file_put_contents($this->getParametersPath(), Yaml::dump(array('parameters' => $symfonyParameters->asArray()),4));
    }

    protected function getParametersPath()
    {
        return $this->container->getParameter('kernel.root_dir') . '/config/parameters.yml';
    }
}