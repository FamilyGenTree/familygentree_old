<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 WorkingDevelopers.NET
 */

namespace FamGeneTree\SetupBundle\Tests\Context;

use FamGeneTree\AppBundle\Tests\AppTestCase;
use FamGeneTree\SetupBundle\Context\Setup\Config\SetupConfig;

class SetupManagerTest extends AppTestCase
{
    protected static $MAIN_STEP_ORDER = array(
        SetupConfig::STEP_LOCALE               => 'fgt.setup.step.locale',
        SetupConfig::STEP_PRE_REQUIREMENTS     => 'fgt.setup.step.pre_requirements',
        SetupConfig::STEP_DATABASE_CREDENTIALS => 'fgt.setup.step.database',
        SetupConfig::STEP_FINISH               => 'fgt.setup.step.finish'
    );

    public function testGetCurrentStep()
    {
        $sut = $this->createSut();
        foreach (array(
                     SetupConfig::STEP_START,
                     SetupConfig::STEP_LOCALE,
                     SetupConfig::STEP_DATABASE_CREDENTIALS,
                     SetupConfig::STEP_PRE_REQUIREMENTS,
                     SetupConfig::STEP_FINISH
                 ) as $step) {
            $sut->setCurrentStep($step);
            $this->assertEquals($step, $sut->getCurrentStep());
        }
    }

    /**
     * @param $fixSteps
     * @param $fixCompleted
     * @param $expectedStep
     *
     * @dataProvider dataProviderTestGetFirstStepCompleted
     */
    public function testGetFirstStepCompleted($fixSteps, $fixCompleted, $expectedStep)
    {
        $sut = $this->createSut($fixSteps);
    }

    public function testGetNextStep()
    {
        $sut = $this->createSut();
        list($stepRoutes, $steps) = $this->getStepOrder($sut);

        $currentStepNumber = 0;
        $this->assertEquals($steps[$currentStepNumber + 1], $sut->getNextStep($steps[$currentStepNumber]));
        $this->assertEquals($stepRoutes[$steps[$currentStepNumber + 1]], $sut->getRouteToStep($sut->getNextStep($steps[$currentStepNumber])));
        $lastStepNumber = count($steps) - 1;
        $this->assertNull($sut->getNextStep($steps[$lastStepNumber]));

        $sut->setCurrentStep(SetupConfig::STEP_START);
        $stepAfterStart = array_search(SetupConfig::STEP_START, $steps) + 1;
        $this->assertNotEquals(SetupConfig::STEP_START, $stepAfterStart);
        $this->assertEquals($steps[$stepAfterStart], $sut->getNextStep());

        $sut->setCurrentStep(SetupConfig::STEP_PRE_REQUIREMENTS);
        $stepAfterRequirements = array_search(SetupConfig::STEP_PRE_REQUIREMENTS, $steps) + 1;
        $this->assertNotEquals(SetupConfig::STEP_PRE_REQUIREMENTS, $stepAfterRequirements);
        $this->assertEquals($steps[$stepAfterRequirements], $sut->getNextStep());
    }

    public function testGetPreviousStep()
    {
        $sut = $this->createSut();
        list($stepRoutes, $steps) = $this->getStepOrder($sut);

        $lastStepNumber = count($steps) - 1;
        $this->assertEquals($steps[$lastStepNumber - 1], $sut->getPreviousStep($steps[$lastStepNumber]));
        $this->assertEquals($stepRoutes[$steps[$lastStepNumber - 1]], $sut->getRouteToStep($sut->getPreviousStep($steps[$lastStepNumber])));
        $firstStepNumber = 0;
        $this->assertNull($sut->getPreviousStep($steps[$firstStepNumber]));
    }

    public function dataProviderTestGetFirstStepCompleted()
    {
        $stepRoutes = static::$MAIN_STEP_ORDER;
        $steps      = array_keys($stepRoutes);

        return array(
            '0 completed'   => array(
                'stepOrder' => $stepRoutes,
                'completed' => array(),
                'expected'  => $steps[0]
            ),
            '1st completed' => array(
                'stepOrder' => $stepRoutes,
                'completed' => array(
                    $steps[0]
                ),
                'expected'  => $steps[1]
            )
        );
    }

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        //$request = Request::create( '/t/1/' );
        //$session = $this->getMock( 'Symfony\Component\HttpFoundation\Session\SessionInterface' );
        //$request->setSession( $session );
        //$this->getContainer()->set( 'request', $request );
        //
        //$this->requestService = $this->container->get( 'ue.render.request' );
    }

    /**
     * @param null $orderStates
     *
     * @return \FamGeneTree\SetupBundle\Context\Setup\SetupManager
     */
    protected function createSut($orderStates = null)
    {
        if ($orderStates === null) {
            $orderStates = self::$MAIN_STEP_ORDER;
        }
        $client = $this->createClient();
        $client->request('GET', '/setup');

        $container = $this->getContainer();
        $container->enterScope('request');
        $container->set('request', $client->getRequest());

        $mock = $this->getMockBuilder('FamGeneTree\SetupBundle\Context\Setup\SetupManager')
                     ->setConstructorArgs([$container])
                     ->setMethods(null)
                     ->getMock();

        $stepOrder = new \ReflectionProperty($mock, 'stepRouteMap');
        $stepOrder->setAccessible(true);
        $stepOrder->setValue($mock, $orderStates);

        return $mock;
    }

    /**
     * @param $sut
     *
     * @return array
     */
    protected function getStepOrder($sut)
    {

        $stepRoutes = static::$MAIN_STEP_ORDER;
        $steps      = array_keys($stepRoutes);

        return array(
            $stepRoutes,
            $steps
        );
    }
}
