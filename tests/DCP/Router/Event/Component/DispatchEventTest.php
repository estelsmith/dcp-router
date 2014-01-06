<?php

namespace tests\DCP\Router\Event\Component;

use DCP\Router\Event\Component\DispatchEvent;
use DCP\Router\Exception\InvalidArgumentException;

class DispatchEventTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsProperInterface()
    {
        $instance = new DispatchEvent();

        $this->assertInstanceOf('DCP\Router\Event\EventInterface', $instance);
    }

    public function testSetComponentThrowsExceptionWhenComponentIsNotAnObject()
    {
        $expectedMessage = 'component must be an object';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new DispatchEvent();
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

        $instance = new DispatchEvent();
        $instance->setComponent($expectedValue);

        $actualValue = $instance->getComponent();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testSetUrlThrowsExceptionWhenUrlIsNotAnArray()
    {
        $expectedMessage = 'url must be an array';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new DispatchEvent();
            $instance->setUrl('not an array');
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testCanSetAndGetUrl()
    {
        $expectedValue = ['testing'];
        $actualValue = null;

        $instance = new DispatchEvent();
        $instance->setUrl($expectedValue);

        $actualValue = $instance->getUrl();

        $this->assertSame($expectedValue, $actualValue);
    }
}
