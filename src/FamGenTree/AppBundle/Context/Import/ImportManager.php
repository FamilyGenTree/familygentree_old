<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGenTree\AppBundle\Context\Import;

use FamGenTree\AppBundle\Context\Configuration\Domain\ConfigKeys;
use FamGenTree\AppBundle\Context\Configuration\Domain\FgtConfig;
use Fgt\Config;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ImportManager
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getImportableGedcoms()
    {
        /** @var FgtConfig $config */
        $config = $this->container->get('fgt.configuration');
        $config->getPathUploads();
        $path   = $config->getPathUploads();

        return glob($path . '/*.{ged,Ged,GED}', GLOB_NOSORT | GLOB_BRACE);
    }
}