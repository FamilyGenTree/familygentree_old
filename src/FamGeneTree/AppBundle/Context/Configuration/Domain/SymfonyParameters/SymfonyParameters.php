<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\AppBundle\Context\Configuration\Domain\SymfonyParameters;

class SymfonyParameters
{

    protected $parameters = array();

    /**
     * @param array|SymfonyParameters|ParametersDatabase $otherParameters
     */
    public function mergeParams($otherParameters)
    {
        if ($otherParameters instanceof SymfonyParameters) {
            $this->parameters = array_merge($this->parameters, $otherParameters->parameters);
        } elseif ($otherParameters instanceof ParametersDatabase) {
            $this->parameters = array_merge(
                $this->parameters,
                array_filter(
                    array(
                        'database_driver'   => $otherParameters->getDbSystem(),
                        'database_host'     => $otherParameters->getHost(),
                        'database_port'     => $otherParameters->getPort(),
                        'database_name'     => $otherParameters->getDbname(),
                        'database_user'     => $otherParameters->getUser(),
                        'database_password' => $otherParameters->getPassword(),
                        'database_prefix'   => $otherParameters->getPrefix()
                    )
                )
            );
        } elseif (is_array($otherParameters)) {
            $this->parameters = array_merge($this->parameters, $otherParameters);
        }
    }

    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    public function getParameter($key)
    {
        return $this->parameters[$key];
    }

    public function asArray()
    {
        return $this->parameters;
    }
}