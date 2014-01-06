<?php

namespace tests\DCP\Router\Event\Component;

use DCP\Router\Event\Component\CreatedEvent;
use DCP\Router\Exception\InvalidArgumentException;

class CreatedEventTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsProperInterface()
    {
        $instance = new CreatedEvent();

        $this->assertInstanceOf('DCP\Router\Event\EventInterface', $instance);
    }

    public function testSetComponentThrowsExceptionWhenComponentIsNotAnObject()
    {
        $expectedMessage = 'component must be an object';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new CreatedEvent();
            $instance->setComponent('not an object');
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testCanSetAndGetComponent()
    {
        $expectedValue = new \stdClass();
        $actualValue = null;

        $instance = new CreatedEvent();
        $instance->setComponent($expectedValue);

        $actualValue = $instance->getComponent();

        $this->assertSame($expectedValue, $actualValue);
    }
}