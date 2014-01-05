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
class MvcRouter extends BaseRouter
{
    protected function setupControllerListeners()
    {
        parent::setupControllerListeners();
        $this->setupControllerDispatchingListener();
    }

    protected function setupControllerDispatchingListener()
    {
        $this->on(ControllerEvents::DISPATCHING, function (Event\Controller\DispatchEvent $event) {
            $controller = $event->getController();
            $url = $event->getUrl();

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
                $event->setUrl($url);
                $event->setMethod($method);
            }
        });
    }
}
