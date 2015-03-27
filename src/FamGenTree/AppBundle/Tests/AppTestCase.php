<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGenTree\AppBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AppTestCase extends WebTestCase
{
    const SKIP_TEST_MARKER = '___SKIP_TEST___';

    /**
     * @return KernelInterface
     */
    protected function getKernel()
    {
        if (null === static::$kernel) {
            static::bootKernel();
        }

        return static::$kernel;
    }

    protected function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    /**
     * @param $object
     * @param $methodName
     *
     * @return \ReflectionMethod
     */
    protected function makeMethodAccessible($object, $methodName)
    {
        $refl = new \ReflectionMethod($object, $methodName);
        $refl->setAccessible(true);

        return $refl;
    }

    protected function checkSkipped($firstParam)
    {
        if (is_string($firstParam) && strpos($firstParam, static::SKIP_TEST_MARKER) === 0) {
            $message = explode(':', $firstParam);
            if (count($message) > 1) {
                $this->markTestSkipped($message[1]);
            } else {
                $this->markTestSkipped();
            }
            return true;
        }
        return false;
    }
}