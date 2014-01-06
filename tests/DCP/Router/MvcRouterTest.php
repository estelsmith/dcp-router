<?php

namespace tests\DCP\Router;

use DCP\Router\ControllerEvents;
use DCP\Router\Event\Controller\DispatchEvent;
use DCP\Router\Exception\NotFoundException;
use DCP\Router\MvcRouter;
use stubs\Controller\TestController;

require_once __DIR__ . '/../../stubs/Controller/TestController.php';

class MvcRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsProperInterface()
    {
        $instance = new MvcRouter();

        $this->assertInstanceOf('DCP\Router\BaseRouter', $instance);
    }

    public function testDefaultControllerDispatchingEventListenerSetsDefaultMethodWhenGivenABlankRoute()
    {
        $controller = new TestController();

        $event = (new DispatchEvent())
            ->setController($controller)
            ->setUrl([])
        ;

        $expectedMessage = 'Could not find stubs\Controller\TestController::indexAction';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new MvcRouter();
            $instance->emit(ControllerEvents::DISPATCHING, [$event]);
        } catch (NotFoundException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testDefaultControllerDispatchingEventListenerThrowsExceptionWhenControllerMethodIsNotFound()
    {
        $controller = new TestController();

        $event = (new DispatchEvent())
            ->setController($controller)
            ->setUrl(['wot', 'test'])
        ;

        $expectedMessage = 'Could not find stubs\Controller\TestController::wotAction';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new MvcRouter();
            $instance->emit(ControllerEvents::DISPATCHING, [$event]);
        } catch (NotFoundException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testDefaultControllerDispatchEventListenerUpdatesEvent()
    {
        $controller = new TestController();

        $event = (new DispatchEvent())
            ->setController($controller)
            ->setUrl(['test', 'ing'])
        ;

        $instance = new MvcRouter();
        $instance->emit(ControllerEvents::DISPATCHING, [$event]);

        $this->assertSame($controller, $event->getController());
        $this->assertEquals('testAction', $event->getMethod());
        $this->assertEquals(['ing'], $event->getUrl());
    }
}