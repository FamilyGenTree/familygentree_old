<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\AppBundle\Service;


use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class Session
{

    protected $_initiated      = false;
    protected $_gedcom         = null;
    protected $_clipboard      = null;
    protected $_timediff       = null;
    protected $_locale         = null;
    protected $_themeId        = null;
    protected $_activity_time  = null;
    protected $_goodToSend     = false;
    protected $_wtUser         = null;
    protected $_statTicks1     = array();
    protected $_statTicks      = array();
    protected $_statisticsPlot = array();
    protected $_cart           = array();
    protected $_CSRF_TOKEN     = null;
    protected $_timeline_pids  = null;

    /**
     * @var ContainerInterface
     */
    protected $diContainer;

    public function __construct(ContainerInterface $diContainer)
    {
        $this->diContainer = $diContainer;
    }

    public function __get($name)
    {
        $name = '_' . $name;

        return isset($this->{$name}) ? $this->{$name} : null;
    }

    public function __set($name, $value)
    {
        $name = '_' . $name;
        if (isset($this->{$name})) {
            $this->{$name} = $value;
        }
    }

    public function __isset($name)
    {
        $name = '_' . $name;
        $properties = get_class_vars(get_class($this));

        return isset($properties[$name]);
    }

    public function __unset($name)
    {
        $name = '_' . $name;
        unset($this->{$name});
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected function getSession()
    {
        return $this->diContainer->get('request')->getSession();
    }

    /**
     * @return FlashBagInterface
     */
    public function getFlashMessageBag() {
        return $this->getSession()->getFlashBag();
    }
}