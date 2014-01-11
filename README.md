#DCP-Router
DCP-Router provides an extensible, event-oriented,  MVC and REST router.

Design goals for the project are always to be concise, simple, extensible, and testable.

[![Build Status](https://travis-ci.org/estelsmith/dcp-router.png?branch=master)](https://travis-ci.org/estelsmith/dcp-router)
[![Coverage Status](https://coveralls.io/repos/estelsmith/dcp-router/badge.png)](https://coveralls.io/r/estelsmith/dcp-router)

## Getting Started
The easiest, and recommended, way of installing the package is [through composer](http://getcomposer.org/).

Just create a composer.json file in your project, and add the following lines:

```json
{
    "require": {
        "dcp/router": "1.0.*"
    }
}
```

Then, run the composer command to install DCP-Router:
```
$ composer install
```

Alternatively, you can clone the repository and install the package manually.

## Basic Usage
### Creating MVC Or REST Routers
The first thing you need to do is instantiate your router. You can choose between an MVC or REST router.
```php
use DCP\Router;

$mvcRouter = new Router\MvcRouter();

$restRouter = new Router\RestRouter();
```

### Route Dispatching
Once you have created the router, you can begin dispatching routes immediately without any other configuration.

By default, the router will look for controller classes in the root namespace. This is configurable, and is shown in
other areas of this README.

For example, if you choose the MVC router to dispatch routes:
```php
use DCP\Router\MvcRouter;

class TestController
{
    public function indexAction($arg = null)
    {
        echo __METHOD__ . ', ' . $arg;
    }
}

class HomeController
{
    public function testAction()
    {
        echo __METHOD__;
    }
}

$router = new MvcRouter();

$router->dispatch('/test/index/hello');
// This will output "TestController::indexAction, hello"

$router->dispatch('/home/test');
// This will output "HomeController::testAction"
```

Alternatively, if you choose the REST router to dispatch routes:
```php
use DCP\Router\RestRouter;

class UsersController
{
    public function get()
    {
        echo __METHOD__;
    }

    public function post($arg = null)
    {
        echo __METHOD__ . ', ' . $arg;
    }
}

$router = new RestRouter();

$router->dispatch('/users/hello', 'post');
// This will output "UsersController::post, hello"

$router->dispatch('/users', 'get');
// This will output "UsersController::get"
```

### Default Routes
When no URL, or a partial URL, is presented to the router, it will assume a default route in order to avoid throwing
a NotFound exception.

In the case of the MVC Router, when no route is presented, it will assume an index controller with an index action. If
a controller is specified, but not an action, it will assume the index action is to be called.
```php
use DCP\Router\MvcRouter;

class IndexController
{
    public function indexAction()
    {
        echo __METHOD__;
    }
}

class HomeController
{
    public function indexAction()
    {
        echo __METHOD__;
    }
}

$router = new MvcRouter();

$router->dispatch('/');
// This will output "IndexController::indexAction"

$router->dispatch('/home');
// This will output "HomeController::indexAction"
```

In the case of the REST router, when no route is presented, it will assume an index controller with a GET method. If a
controller is specified, but not an HTTP method, it will assume the GET method is to be called.
```php
use DCP\Router\RestRouter;

class IndexController
{
    public function get()
    {
        echo __METHOD__;
    }
}

class HomeController
{
    public function get()
    {
        echo __METHOD__;
    }

    public function post()
    {
        echo __METHOD__;
    }
}

$router = new RestRouter();

$router->dispatch('/');
// This will output "IndexController::get"

$router->dispatch('/home');
// This will output "HomeController::get"

$router->dispatch('/home', 'post');
// This will output "HomeController::post"
```

### Not Found Errors
When the router is not able to find a specified resource, it will throw a NotFoundException, allowing you to display
a 404 page if desired.

```php
use DCP\Router\MvcRouter;

$router = new MvcRouter(); // RestRouter can be used here, too.

try {
    $router->dispatch('/home');
} catch (NotFoundException $e) {
    echo 'Could not find /home!!';
}
```

### Controller Namespace Prefixes
When your application becomes complex enough to require structuring with namespaces, you can begin setting namespace
prefixes for the router use. The router will look for controllers in whatever namespace you specify.

```php
// App/Controller/TestController.php
namespace App\Controller;

class TestController
{
    public function helloAction()
    {
        echo 'Hello, world!';
    }
}
```

```php
// index.php
use DCP\Router\MvcRouter;

$router = new MvcRouter();
$router->setControllerPrefix('App\Controller');

$router->dispatch('/test/hello');
// This will output "Hello, world!"
```

### Router Hierarchy
DCP-Router gives you the ability to create separate site areas with their own URL prefix, such as an administrative
site section living under `/admin`.

In order to facilitate this type of functionality, the project relies on hierarchical routing, where the router will
pass the URL off to other router instances when a known prefix is seen.

As an example, we will create a controller that should only be reachable by a `/admin` URL prefix.
```php
// App/Admin/Controller/TestController.php
namespace App\Admin\Controller;

class TestController
{
    public function helloAction()
    {
        echo 'Hello, world!';
    }
}
```

Then, we will create an `AdminRouter` class to facilitate routing to the `App\Admin\Controller` namespace of the site.
```php
// App/Admin/AdminRouter.php
namespace App\Admin;

use DCP\Router\MvcRouter;

class AdminRouter extends MvcRouter
{
    public function __construct()
    {
        parent::__construct();
        $this->setControllerPrefix('App\Admin\Controller');
    }
}
```

Finally, we instantiate a router, and tell it that anything under the `/admin` URL prefix should be handled by
`AdminRouter`.
```php
// index.php

use DCP\Router\MvcRouter;

$router = new MvcRouter();
$router->setComponents([
    'admin' => 'App\Admin\AdminRouter'
]);

$router->dispatch('/admin/test/hello');
// This will output "Hello, world!"
```

When the router is told to route to `/admin/test/hello`, it will see that the first piece of the URL is `admin`. Since
a component was registered with the `admin` key, the router will dispatch the `/test/hello` portion of the route to
`AdminRouter`, which will then end up calling `TestController#helloAction()`.

## Detailed Usage
### Event System
The core of DCP-Router utilizes [Evenement](https://github.com/igorw/evenement) and has events for every step of the
routing process. This allows you to tie in to any point of the process and execute custom logic, if needed.

There are a total of twelve events, six that are called when a controller is dispatched, and six that are called when
a secondary router is dispatched. Events are called in the order listed for their respective areas.

Controller events:
- `ControllerEvents::CREATING` is emitted when the router is resolving the fully qualified classname of the controller
that is being dispatched to.
    - This event can be used for custom controller resolving logic.
- `ControllerEvents::CREATE` is emitted when the router instantiates the controller.
    - This event can be used for custom class creation logic.
- `ControllerEvents::CREATED` is emitted after the router has instantiated the controller.
- `ControllerEvents::DISPATCHING` is emitted when the router is resolving the method name to call on the controller.
    - This event can be used for custom controller method resolving logic.
- `ControllerEvents::DISPATCH` is emitted when the router called the resolved controller method.
    - This event can be used for custom controller action dispatch logic.
- `ControllerEvents::DISPATCHED` is emitted after the router has called the resolved controller method.

Component events:
- `ComponentEvents::CREATING` is emitted when the router is resolving the fully qualified classname of the component
router to dispatch to. By default, it is the class name specified in the `*Router#setComponents()` call.
- `ComponentEvents::CREATE` is emitted when the router instantiates the component router.
    - This event can be used for custom class creation logic.
- `ComponentEvents::CREATED` is emitted after the router instantiates the component router.
- `ComponentEvents::DISPATCHING` is emitted when the router is preparing to pass control over to the component router.
- `ComponentEvents::DISPATCH` is emitted when the router hands control over to the component router.
    - This event can be used to provide custom component dispatch logic.
- `ComponentEvents::DISPATCHED` is emitted after the router has handed control over to the component router, and the
component router has finished.

To tie into an event, you can simply attach a listener for the event. It will be called when the router enters the
specific stage of routing.

```php
use DCP\Router\MvcRouter;
use DCP\Router\ControllerEvents;
use DCP\Router\Event\CreatingEvent;

class TestControllerHi
{
    public function indexAction()
    {
        echo 'Hello!!'
    }
}

$router = new MvcRouter();
$router->on(ControllerEvents::CREATING, function (CreatingEvent $event) {
    $event->setClass($event->getClass() . 'Hi');
});

$router->dispatch('/test');
// This will output "Hello!!"
```

### Coupling With Dependency Injection Containers
One important aspect of the event system is that it allows for easy integration into various dependency injection
containers.

The `ControllerEvents::CREATE` and `ComponentEvents::CREATE` events are specifically for instantiating a class, so they
can be overridden to pull instances out of a DI container.

```php
use DCP\Router\MvcRouter;
use DCP\Router\ControllerEvents;
use DCP\Router\ComponentEvents;
use DCP\Di\Container;
use DCP\Di\ContainerAwareInterface;

class DiMvcRouter extends MvcRouter implements ContainerAwareInterface
{
    protected $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function __construct()
    {
        parent::__construct();

        $createCallback = function (CreateEvent $event) {
            $container = $this->container;
            $class = $event->getClass();
            $event->setInstance($container->get($class));
        };

        $this->removeAllListeners(ControllerEvents::CREATE);
        $this->on(ControllerEvents::CREATE, $createCallback);

        $this->removeAllListeners(ComponentEvents::CREATE);
        $this->on(ComponentEvents::CREATE, $createCallback);
    }
}
```

## Contributing
If you would like to contribute to DCP-Router, you can do so in one of two ways:
- Submit issues for bugs you find, or functionality that would improve the project.
- Fork the repository, and submit a pull request.

## Testing
DCP-Router utilizes PHPUnit 3.7.x for automated testing.

All changes to the codebase are accompanied by unit tests.