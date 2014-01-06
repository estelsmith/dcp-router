<?php

namespace tests\DCP\Router;

use ConcreteRouter as BaseRouter;
use DCP\Router\ComponentEvents;
use DCP\Router\ControllerEvents;
use DCP\Router\Event;
use DCP\Router\Exception\InvalidArgumentException;
use DCP\Router\Exception\NotFoundException;
use stubs\Controller\TestController;

require_once __DIR__ . '/../../stubs/ConcreteRouter.php';
require_once __DIR__ . '/../../stubs/TestRouter.php';
require_once __DIR__ . '/../../stubs/Controller/TestController.php';

class BaseRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsProperInterface()
    {
        $instance = new BaseRouter();

        $this->assertInstanceOf('DCP\Router\BaseRouterInterface', $instance);
        $this->assertInstanceOf('Evenement\EventEmitterInterface', $instance);
    }

    public function testSetComponentsThrowsExceptionWhenComponentsIsNotAnArray()
    {
        $expectedMessage = 'components must be an array';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new BaseRouter();
            $instance->setComponents('not an array');
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testCanSetAndGetComponents()
    {
        $expectedValue = ['test'];
        $actualValue = null;

        $instance = new BaseRouter();
        $instance->setComponents($expectedValue);

        $actualValue = $instance->getComponents();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testSetControllerPrefixThrowsExceptionWhenPrefixIsNotAString()
    {
        $expectedMessage = 'prefix must be a string';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new BaseRouter();
            $instance->setControllerPrefix(['not a string']);
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testCanSetAndGetControllerPrefix()
    {
        $expectedValue = 'test';
        $actualValue = null;

        $instance = new BaseRouter();
        $instance->setControllerPrefix($expectedValue);

        $actualValue = $instance->getControllerPrefix();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testDispatchThrowsExceptionWhenUrlIsNotAStringOrArray()
    {
        $expectedMessage = 'url must be a string or array';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new BaseRouter();
            $instance->dispatch(new \stdClass);
        } catch (InvalidArgumentException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testDefaultControllerCreatingEventListenerThrowsExceptionWhenControllerIsNotFound()
    {
        $expectedMessage = 'Could not find stubs\Controller\NopeController';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new BaseRouter();
            $instance->setControllerPrefix('stubs\Controller');

            $instance->emit(ControllerEvents::CREATING, [
                (new Event\CreatingEvent())
                    ->setName('nope')
            ]);
        } catch (NotFoundException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testDefaultControllerCreatingEventListenerSetsClassWhenControllerIsFound()
    {
        $expectedClass = 'stubs\Controller\TestController';
        $actualClass = '';

        $instance = new BaseRouter();
        $instance->setControllerPrefix('stubs\Controller');

        $event = (new Event\CreatingEvent())
            ->setName('test')
        ;

        $instance->emit(ControllerEvents::CREATING, [$event]);

        $actualClass = $event->getClass();

        $this->assertEquals($expectedClass, $actualClass);
    }

    public function testDefaultControllerCreateEventListenerCreatesController()
    {
        $expectedInstance = 'stubs\Controller\TestController';
        $actualInstance = '';

        $instance = new BaseRouter();
        $instance->setControllerPrefix('stubs\Controller');

        $event = (new Event\CreateEvent())
            ->setClass($expectedInstance)
        ;

        $instance->emit(ControllerEvents::CREATE, [$event]);

        $actualInstance = $event->getInstance();

        $this->assertInstanceOf($expectedInstance, $actualInstance);
    }

    public function testDefaultControllerDispatchEventListenerCallsControllerMethod()
    {
        $expectedArg = 'test';
        $actualArg = '';

        $controller = new TestController();

        $event = (new Event\Controller\DispatchEvent())
            ->setController($controller)
            ->setMethod('testAction')
            ->setUrl([$expectedArg])
        ;

        $instance = new BaseRouter();
        $instance->emit(ControllerEvents::DISPATCH, [$event]);

        $actualArg = $controller->arg;

        $this->assertEquals($expectedArg, $actualArg);
    }

    public function testDefaultComponentCreatingEventListenerSetsClass()
    {
        $expectedClass = 'test';
        $actualClass = '';

        $event = (new Event\CreatingEvent())
            ->setName($expectedClass)
        ;

        $instance = new BaseRouter();
        $instance->emit(ComponentEvents::CREATING, [$event]);

        $actualClass = $event->getClass();

        $this->assertEquals($expectedClass, $actualClass);
    }

    public function testDefaultComponentCreateEventListenerCreatesComponent()
    {
        $expectedInstance = '\ConcreteRouter';
        $actualInstance = '';

        $event = (new Event\CreateEvent())
            ->setClass($expectedInstance)
        ;

        $instance = new BaseRouter();
        $instance->emit(ComponentEvents::CREATE, [$event]);

        $actualInstance = $event->getInstance();

        $this->assertInstanceOf($expectedInstance, $actualInstance);
    }

    public function testDefaultComponentDispatchEventListenerDispatchesUrl()
    {
        $expectedUrl = ['test', 'me'];
        $actualUrl = '';

        $component = new \TestRouter();

        $event = (new Event\Component\DispatchEvent())
            ->setComponent($component)
            ->setUrl($expectedUrl)
        ;

        $instance = new BaseRouter();
        $instance->emit(ComponentEvents::DISPATCH, [$event]);

        $actualUrl = $component->url;

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testDispatchWithBlankRouteFiresAllControllerListeners()
    {
        $instance = new BaseRouter();
        $instance->removeAllListeners();

        $instance->on(ControllerEvents::CREATING, function (Event\CreatingEvent $event) {
            $this->assertEquals('index', $event->getName());
            $event->setClass('stubs\Controller\TestController');
        });

        $instance->on(ControllerEvents::CREATE, function (Event\CreateEvent $event) {
            $this->assertEquals('stubs\Controller\TestController', $event->getClass());
            $event->setInstance(new TestController());
        });

        $instance->on(ControllerEvents::CREATED, function (Event\Controller\CreatedEvent $event) {
            $this->assertInstanceOf('stubs\Controller\TestController', $event->getController());
        });

        $instance->on(ControllerEvents::DISPATCHING, function (Event\Controller\DispatchEvent $event) {
            $this->assertInstanceOf('stubs\Controller\TestController', $event->getController());
            $event->setMethod('indexAction');
            $event->setUrl(['test']);
        });

        $instance->on(ControllerEvents::DISPATCH, function (Event\Controller\DispatchEvent $event) {
            $this->assertInstanceOf('stubs\Controller\TestController', $event->getController());
            $this->assertEquals('indexAction', $event->getMethod());
            $this->assertEquals(['test'], $event->getUrl());
        });

        $instance->on(ControllerEvents::DISPATCHED, function (Event\Controller\DispatchEvent $event) {
            $this->assertInstanceOf('stubs\Controller\TestController', $event->getController());
            $this->assertEquals('indexAction', $event->getMethod());
            $this->assertEquals(['test'], $event->getUrl());
        });

        $instance->dispatch('');
    }

    public function testDispatchWithRouteFiresAllControllerListeners()
    {
        $instance = new BaseRouter();
        $instance->removeAllListeners();

        $instance->on(ControllerEvents::CREATING, function (Event\CreatingEvent $event) {
            $this->assertEquals('test', $event->getName());
            $event->setClass('stubs\Controller\TestController');
        });

        $instance->on(ControllerEvents::CREATE, function (Event\CreateEvent $event) {
            $this->assertEquals('stubs\Controller\TestController', $event->getClass());
            $event->setInstance(new TestController());
        });

        $instance->on(ControllerEvents::CREATED, function (Event\Controller\CreatedEvent $event) {
            $this->assertInstanceOf('stubs\Controller\TestController', $event->getController());
        });

        $instance->on(ControllerEvents::DISPATCHING, function (Event\Controller\DispatchEvent $event) {
            $this->assertInstanceOf('stubs\Controller\TestController', $event->getController());
            $this->assertEquals(['test', 'lol'], $event->getUrl());
            $event->setMethod('testAction');
            $event->setUrl(['lol']);
        });

        $instance->on(ControllerEvents::DISPATCH, function (Event\Controller\DispatchEvent $event) {
            $this->assertInstanceOf('stubs\Controller\TestController', $event->getController());
            $this->assertEquals('testAction', $event->getMethod());
            $this->assertEquals(['lol'], $event->getUrl());
        });

        $instance->on(ControllerEvents::DISPATCHED, function (Event\Controller\DispatchEvent $event) {
            $this->assertInstanceOf('stubs\Controller\TestController', $event->getController());
            $this->assertEquals('testAction', $event->getMethod());
            $this->assertEquals(['lol'], $event->getUrl());
        });

        $instance->dispatch('/test/test/lol');
    }

    public function testDispatchWithComponentFiresAllComponentListeners()
    {
        $instance = new BaseRouter();
        $instance->removeAllListeners();
        $instance->setComponents([
            'test' => 'test component'
        ]);

        $instance->on(ComponentEvents::CREATING, function (Event\CreatingEvent $event) {
            $this->assertEquals('test component', $event->getName());
            $event->setClass($event->getName());
        });

        $instance->on(ComponentEvents::CREATE, function (Event\CreateEvent $event) {
            $this->assertEquals('test component', $event->getClass());
            $event->setInstance(new \TestRouter());
        });

        $instance->on(ComponentEvents::CREATED, function (Event\Component\CreatedEvent $event) {
            $this->assertInstanceOf('\TestRouter', $event->getComponent());
        });

        $instance->on(ComponentEvents::DISPATCHING, function (Event\Component\DispatchEvent $event) {
            $this->assertInstanceOf('\TestRouter', $event->getComponent());
            $this->assertEquals(['me', 'out'], $event->getUrl());
        });

        $instance->on(ComponentEvents::DISPATCH, function (Event\Component\DispatchEvent $event ) {
            $this->assertInstanceOf('\TestRouter', $event->getComponent());
            $this->assertEquals(['me', 'out'], $event->getUrl());
        });

        $instance->on(ComponentEvents::DISPATCHED, function (Event\Component\DispatchEvent $event) {
            $this->assertInstanceOf('\TestRouter', $event->getComponent());
            $this->assertEquals(['me', 'out'], $event->getUrl());
        });

        $instance->dispatch('/test/me/out');
    }
}