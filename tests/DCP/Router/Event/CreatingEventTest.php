<?php

namespace tests\DCP\Router\Event;

use DCP\Router\Event\CreatingEvent;
use DCP\Router\Exception\InvalidArgumentException;

class CreatingEventTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsProperInterface()
    {
        $instance = new CreatingEvent();

        $this->assertInstanceOf('DCP\Router\Event\EventInterface', $instance);
    }

    public function testSetNameThrowsExceptionWhenNameIsNotAString()
    {
        $expectedMessage = 'name must be a string';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new CreatingEvent();
            $instance->setName(['not a string']);
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testCanSetAndGetName()
    {
        $expectedValue = 'test';
        $actualValue = null;

        $instance = new CreatingEvent();
        $instance->setName($expectedValue);

        $actualValue = $instance->getName();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testSetClassThrowsExceptionWhenClassIsNotAString()
    {
        $expectedMessage = 'class must be a string';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new CreatingEvent();
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

        $instance = new CreatingEvent();
        $instance->setClass($expectedValue);

        $actualValue = $instance->getClass();

        $this->assertSame($expectedValue, $actualValue);
    }
}
