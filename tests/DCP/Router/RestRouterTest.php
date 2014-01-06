<?php

namespace tests\DCP\Router;

use DCP\Router\ComponentEvents;
use DCP\Router\Event\Component\DispatchEvent;
use DCP\Router\Exception\NotFoundException;
use DCP\Router\RestRouter;

require_once __DIR__ . '/../../stubs/TestRouter.php';
require_once __DIR__ . '/../../stubs/Controller/TestController.php';

class RestRouterTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsProperInterface()
    {
        $instance = new RestRouter();

        $this->assertInstanceOf('DCP\Router\BaseRouter', $instance);
    }

    public function testDispatchThrowsExceptionWhenControllerMethodNotFound()
    {
        $expectedMessage = 'Could not find stubs\Controller\TestController::post';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new RestRouter();
            $instance->setControllerPrefix('stubs\Controller');

            $instance->dispatch('/test', 'POST');
        } catch (NotFoundException $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testDispatchCallsControllerMethod()
    {
        $expectedMessage = 'weeeh';
        $actualMessage = '';
        $gotException = false;

        try {
            $instance = new RestRouter();
            $instance->setControllerPrefix('stubs\Controller');

            $instance->dispatch('/test/weeeh', 'GET');
        } catch (\Exception $e) {
            $gotException = true;
            $actualMessage = $e->getMessage();
        }

        $this->assertTrue($gotException);
        $this->assertEquals($expectedMessage, $actualMessage);
    }

    public function testDispatchDoesNotThrowException()
    {
        $gotException = false;

        try {
            $instance = new RestRouter();
            $instance->setControllerPrefix('stubs\Controller');

            $instance->dispatch('/test/weeeh', 'PUT');
        } catch (\Exception $e) {
            $gotException = true;
        }

        $this->assertFalse($gotException);
    }

    public function testDispatchPassesMethodToComponents()
    {
        $expectedMethod = 'put';
        $actualMethod = '';

        $instance = new RestRouter();
        $instance->setComponents([
            'test' => '\TestRouter'
        ]);

        $instance->on(ComponentEvents::DISPATCH, function (DispatchEvent $event) use (&$actualMethod) {
            $actualMethod = $event->getComponent()->method;
        });

        $instance->dispatch('/test', 'PUT');

        $this->assertEquals($expectedMethod, $actualMethod);
    }
}