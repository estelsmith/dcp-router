<?php

namespace tests\DCP\Router\Event;

use DCP\Router\Event\CreateEvent;
use DCP\Router\Exception\InvalidArgumentException;

class CreateEventTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsProperInterface()
    {
        $instance = new CreateEvent();

        $this->assertInstanceOf('DCP\Router\Event\EventInterface', $instance);
    }

    public function testSetClassThrowsExceptionWhenClassIsNotAString()
    {
        $expectedMessage = 'class must be a string';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new CreateEvent();
            $instance->setClass(['not a string']);
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testCanSetAndGetClass()
    {
        $expectedValue = 'test';
        $actualValue = null;

        $instance = new CreateEvent();
        $instance->setClass($expectedValue);

        $actualValue = $instance->getClass();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testSetInstanceThrowsExceptionWhenInstanceIsNotAnObject()
    {
        $expectedMessage = 'instance must be an object';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new CreateEvent();
            $instance->setInstance('not an object');
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testCanSetAndGetInstance()
    {
        $expectedValue = new \stdClass();
        $actualValue = null;

        $instance = new CreateEvent();
        $instance->setInstance($expectedValue);

        $actualValue = $instance->getInstance();

        $this->assertSame($expectedValue, $actualValue);
    }
}
