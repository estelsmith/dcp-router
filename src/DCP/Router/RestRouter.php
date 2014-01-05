<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

use DCP\Router\Exception\NotFoundException;

/**
 * Provides a very minimalistic MVC-style router.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class RestRouter extends BaseRouter
{
    protected $method;

    public function dispatch($url, $method = 'get')
    {
        $this->method = strtolower($method);
        return parent::dispatch($url);
    }

    protected function setupControllerListeners()
    {
        parent::setupControllerListeners();
        $this->setupControllerDispatchingListener();
    }

    protected function setupControllerDispatchingListener()
    {
        $this->on(ControllerEvents::DISPATCHING, function (Event\Controller\DispatchEvent $event) {
            $controller = $event->getController();
            $method = $this->method;

            if (!method_exists($controller, $method)) {
                $class_name = get_class($controller);

                throw new NotFoundException('Could not find ' . $class_name . '::' . $method);
            } else {
                $event->setMethod($method);
            }
        });
    }

    protected function setupComponentListeners()
    {
        parent::setupComponentListeners();

        $this->removeAllListeners(ComponentEvents::DISPATCH);
        $this->on(ComponentEvents::DISPATCH, function (Event\Component\DispatchEvent $event) {
            $component = $event->getComponent();
            $component->dispatch($event->getUrl(), $this->method);
        });
    }
}