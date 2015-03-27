<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck;

use FamGenTree\AppBundle\Context\Configuration\Domain\ConfigKeys;
use FamGenTree\SetupBundle\Context\Setup\Step\PreRequirementCheck\ValueObject\PreRequirementResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckPhpDisabledFunctions extends CheckAbstract
{


    protected $disabled_functions = array(
        'parse_ini_file'
    );

    function __construct(ContainerInterface $container)
    {
        parent::__construct($container, 'Needed Php Functions Potentially Disabled', 'Description');
    }

    /**
     * @return null
     */
    public function run()
    {
        $disable_functions = preg_split('/ *, */', ini_get('disable_functions'));
        foreach (array('parse_ini_file') as $function) {
            $state   = PreRequirementResult::STATE_SUCCESS;
            $message = null;
            if (in_array($function, $disable_functions)) {
                $state   = PreRequirementResult::STATE_FAILED;
                $message = 'is disabled on this server.  You cannot install '
                           . $this->container->get('fgt.setup.configuration')
                                             ->get(ConfigKeys::SYSTEM_NAME)
                           . ' until it is enabled. Please ask your server’s administrator to enable it.';

            }
            $this->addResult(
                new PreRequirementResult(
                    "{$function}()",
                    $state,
                    $message
                )
            );
        }
    }
}