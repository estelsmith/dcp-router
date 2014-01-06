<?php

namespace tests\DCP\Router\Event\Controller;

use DCP\Router\Event\Controller\CreatedEvent;
use DCP\Router\Exception\InvalidArgumentException;

class CreatedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsProperInterface()
    {
        $instance = new CreatedEvent();

        $this->assertInstanceOf('DCP\Router\Event\EventInterface', $instance);
    }

    public function testSetControllerThrowsExceptionWhenControllerIsNotAnObject()
    {
        $expectedMessage = 'controller must be an object';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new CreatedEvent();
            $instance->setController('not an object');
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testCanSetAndGetController()
    {
        $expectedComponent = new \stdClass();
        $actualComponent = null;

        $instance = new CreatedEvent();
        $instance->setController($expectedComponent);

        $actualComponent = $instance->getController();

        $this->assertSame($expectedComponent, $actualComponent);
    }
}
