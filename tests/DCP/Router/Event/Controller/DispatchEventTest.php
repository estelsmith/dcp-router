<?php

namespace tests\DCP\Router\Event\Controller;

use DCP\Router\Event\Controller\DispatchEvent;
use DCP\Router\Exception\InvalidArgumentException;

class DispatchEventTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsProperInterface()
    {
        $instance = new DispatchEvent();

        $this->assertInstanceOf('DCP\Router\Event\EventInterface', $instance);
    }

    public function testSetControllerThrowsExceptionWhenControllerIsNotAnObject()
    {
        $expectedMessage = 'controller must be an object';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new DispatchEvent();
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
        $expectedValue = new \stdClass();
        $actualValue = null;

        $instance = new DispatchEvent();
        $instance->setController($expectedValue);

        $actualValue = $instance->getController();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testSetMethodThrowsExceptionWhenMethodIsNotAString()
    {
        $expectedMessage = 'method must be a string';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new DispatchEvent();
            $instance->setMethod(['not a string']);
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testCanSetAndGetMethod()
    {
        $expectedValue = 'test';
        $actualValue = null;

        $instance = new DispatchEvent();
        $instance->setMethod($expectedValue);

        $actualValue = $instance->getMethod();

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
        $expectedValue = ['test'];
        $actualValue = null;

        $instance = new DispatchEvent();
        $instance->setUrl($expectedValue);

        $actualValue = $instance->getUrl();

        $this->assertSame($expectedValue, $actualValue);
    }
}
