<?php
/* Copyright (c) 2013 Estel Smith
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

use DCP\Router\Exception\NotFoundException;
use Evenement\EventEmitterInterface;

/**
 * Provides a very minimalistic MVC-style router.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class MvcRouter extends BaseRouter
{
    /**
     * @param array $components Array of components to attach to the router.
     * @param string $controller_prefix Namespace prefix to apply to all controllers being routed to.
    */
    public function __construct(EventEmitterInterface $emitter, $components = null, $controller_prefix = null)
    {
        parent::__construct($emitter, $components, $controller_prefix);

        $this->setupControllerListeners();
        $this->setupComponentListeners();
    }

    protected function setupControllerListeners()
    {
        $emitter = $this->emitter;
        $router = $this;

        $emitter->on(self::EVENT_CONTROLLER_CREATE, function ($controller_name, $url) use ($emitter, $router) {
            $controller_prefix = $router->getControllerPrefix();
            $class_name = $controller_prefix . '\\' . ucfirst($controller_name) . 'Controller';
            $controller = null;

            if (!class_exists($class_name)) {
                throw new NotFoundException('Could not find ' . $class_name);
            } else {
                $controller = new $class_name();
                $emitter->emit($router::EVENT_CONTROLLER_CREATED, array($controller, $url));
            }
        });

        $emitter->on(self::EVENT_CONTROLLER_DISPATCH, function ($controller, $url) use ($emitter, $router) {
            // Set the default action, in case no action was specified in the URL.
            $method = 'index';

            // Get action from URL if it exists.
            if (count($url) > 0) {
                $method = array_shift($url);
            }

            $method .= 'Action';

            if (!method_exists($controller, $method)) {
                $class_name = get_class($controller);

                throw new NotFoundException('Could not find ' . $class_name . '::' . $method);
            } else {
                $result = call_user_func_array(array($controller, $method), $url);
                $emitter->emit($router::EVENT_CONTROLLER_DISPATCHED, array($result, $controller, $method));
            }
        });
    }

    protected function setupComponentListeners()
    {
        $emitter = $this->emitter;
        $created = self::EVENT_COMPONENT_CREATED;
        $dispatched = self::EVENT_COMPONENT_DISPATCHED;

        $emitter->on(self::EVENT_COMPONENT_CREATE, function ($component_name, $url) use ($emitter, $created) {
            $component = new $component_name($emitter);
            $emitter->emit($created, array($component, $url));
        });

        $emitter->on(self::EVENT_COMPONENT_DISPATCH, function ($component, $url) use ($emitter, $dispatched) {
            $result = $component->dispatch($url);
            $emitter->emit($dispatched, array($result, $component, $url));
        });
    }
}
