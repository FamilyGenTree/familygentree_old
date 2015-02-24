<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Context\Setup\Service\PreRequirementCheck;

use FamGeneTree\SetupBundle\Context\Setup\ValueObject\PreRequirementResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CheckPhpIniSettings extends CheckAbstract
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container, 'Checking server capacity', <<<'TAG'
The memory and CPU time requirements depend on the number of individuals in your family tree.

The following list shows typical requirements:

Small systems (500 individuals): 16–32 MB, 10–20 seconds
Medium systems (5,000 individuals): 32–64 MB, 20–40 seconds
Large systems (50,000 individuals): 64–128 MB, 40–80 seconds

If you try to exceed these limits, you may experience server time-outs and blank pages.

If your server's security policy permits it, you will be able to request increased memory or CPU time using the webtrees administration page.
  Otherwise, you will need to contact your server’s administrator.
TAG
        );
    }


    protected $settings = array(
        'file_uploads' => array(
            'set'           => 'needed',
            'value'         => null,
            'feature'       => 'file upload capability',
            'error-message' => 'PHP setting “%1$s” is disabled.  Without it, the following features will not work: %2$s.  Please ask your server’s administrator to enable it.'
        ),
    );

    /**
     * @return null
     */
    public function run()
    {
        foreach ($this->settings as $key => $requirements) {
        }
        $this->memoryLimitCheck();
    }

    protected function memoryLimitCheck()
    {
        // Previously, we tried to determine the maximum value that we could set for these values.
        // However, this is unreliable, especially on servers with custom restrictions.
        // Now, we just show the default values.  These can (hopefully!) be changed using the
        // site settings page.
        $memory_limit = ini_get('memory_limit');
        if (substr_compare($memory_limit, 'M', -1) === 0) {
            $memory_limit = substr($memory_limit, 0, -1);
        } elseif (substr_compare($memory_limit, 'K', -1) === 0) {
            $memory_limit = substr($memory_limit, 0, -1) / 1024;
        } elseif (substr_compare($memory_limit, 'G', -1) === 0) {
            $memory_limit = substr($memory_limit, 0, -1) * 1024;
        }
        $max_execution_time = ini_get('max_execution_time');
        $this->addResult(
            new PreRequirementResult(
                'memory_limit',
                $memory_limit < 32
                    ? PreRequirementResult::STATE_WARNING
                    : PreRequirementResult::STATE_OK,
                sprintf('This server\'s memory limit is %d MB', $memory_limit)
            )
        );
        $this->addResult(
            new PreRequirementResult(
                'max_execution_time',
                $max_execution_time > 0 && $max_execution_time < 20
                    ? PreRequirementResult::STATE_WARNING
                    : PreRequirementResult::STATE_OK,
                sprintf('CPU time limit is %d seconds.', $max_execution_time)
            )
        );
    }
}