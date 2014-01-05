<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use DCP\Router\Exception\NotFoundException;

/**
 * Provides a very minimalistic MVC-style router.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
abstract class BaseRouter implements BaseRouterInterface, EventEmitterInterface
{
    use EventEmitterTrait;

    /**
     * Listing of components that the router may route to.
     * @var array
    */
    protected $components = array();

    /**
     * Namespace prefix to apply to all controllers being routed to.
     * @var string
     */
    protected $controllerPrefix = '';

    public function __construct()
    {
        $this->setupControllerListeners();
        $this->setupComponentListeners();
    }

    /**
     * Retrieve the components that have been attached to the router.
     * @return array
    */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * Attach an array of components to the router.
     * @param array $components
     * @return $this
    */
    public function setComponents($components)
    {
        $this->components = $components;
        return $this;
    }

    /**
     * Retrieve the namespace prefix being applied to controllers being routed to.
     * @return string
     */
    public function getControllerPrefix()
    {
        return $this->controllerPrefix;
    }

    /**
     * Set the namespace prefix to apply to all controllers being routed to.
     * @param string $prefix
     * @return $this
     */
    public function setControllerPrefix($prefix)
    {
        $this->controllerPrefix = $prefix;
        return $this;
    }

    /**
     * Dispatch URL to application controller.
     * @return mixed
     */
    public function dispatch($url)
    {
        $return_value = false;

        if (!is_array($url)) {
            $url = $this->convertUrlToArray($url);
        }

        /*
         * Route to the index controller if no route was presented.
         *
         * Otherwise, route to an appropriate component, if it exists, or route to the appropriate
         * controller if there is no acceptable component to route to.
        */
        if (!$url) {
            $this->dispatchController('index', array());
        } else {
            $node = array_shift($url);

            if (isset($this->components[$node])) {
                $this->dispatchComponent($node, $url);
            } else {
                $this->dispatchController($node, $url);
            }
        }

        return $return_value;
    }

    /**
     * Converts a URL to an array for routing purposes. Returns the array on success, or FALSE on failure.
     * @return array|false
    */
    protected function convertUrlToArray($url)
    {
        $return_value = false;

        if ($url) {
            // Remove the query string, if it exists.
            $url = explode('?', $url);
            $url = $url[0];

            // Break URL into an array.
            $url = explode('/', $url);

            /*
             * Loop through the URL array and remove any empty entries caused by multiple consecutive slashes.
            */
            if ($url) {
                foreach ($url as $url_key => $url_entry) {
                    if ($url_entry === '') {
                        unset($url[$url_key]);
                    }
                }

                // Re-index array, just in case it looks like [1 => 1, 2 => 2, 5 => 5]
                $url = array_values($url);
            }

            // Return the URL if there is anything left after processing.
            if (count($url) > 0) {
                $return_value = $url;
            }
        }

        return $return_value;
    }

    protected function dispatchController($node, $url)
    {
        $event = (new Event\CreatingEvent())
            ->setName($node)
        ;
        $this->emit(ControllerEvents::CREATING, array($event));

        $event = (new Event\CreateEvent())
            ->setClass($event->getClass())
        ;
        $this->emit(ControllerEvents::CREATE, array($event));

        $controller = $event->getInstance();

        $this->emit(ControllerEvents::CREATED, array(
            (new Event\Controller\CreatedEvent())
                ->setController($controller)
        ));

        $event = (new Event\Controller\DispatchEvent())
            ->setController($controller)
            ->setUrl($url)
        ;

        $this->emit(ControllerEvents::DISPATCHING, array($event));
        $this->emit(ControllerEvents::DISPATCH, array($event));
        $this->emit(ControllerEvents::DISPATCHED, array($event));
    }

    protected function setupComponentListeners()
    {
        $this->on(ComponentEvents::CREATING, function (Event\CreatingEvent $event) {
            $event->setClass($event->getName());
        });

        $this->on(ComponentEvents::CREATE, function (Event\CreateEvent $event) {
            $class = $event->getClass();
            $event->setInstance(new $class());
        });

        $this->on(ComponentEvents::DISPATCH, function (Event\Component\DispatchEvent $event) {
            $component = $event->getComponent();
            $component->dispatch($event->getUrl());
        });
    }

    protected function dispatchComponent($node, $url)
    {
        $componentName = $this->components[$node];

        $event = (new Event\CreatingEvent())
            ->setName($componentName)
        ;
        $this->emit(ComponentEvents::CREATING, array($event));

        $event = (new Event\CreateEvent())
            ->setClass($event->getClass())
        ;
        $this->emit(ComponentEvents::CREATE, array($event));

        $component = $event->getInstance();

        $this->emit(ComponentEvents::CREATED, array(
            (new Event\Component\CreatedEvent())
                ->setComponent($component)
        ));

        $event = (new Event\Component\DispatchEvent())
            ->setComponent($component)
            ->setUrl($url)
        ;
        $this->emit(ComponentEvents::DISPATCHING, array($event));
        $this->emit(ComponentEvents::DISPATCH, array($event));
        $this->emit(ComponentEvents::DISPATCHED, array($event));
    }

    protected function setupControllerListeners()
    {
        $this->setupControllerCreatingListener();
        $this->setupControllerCreateListener();
        $this->setupControllerDispatchListener();
    }

    protected function setupControllerCreatingListener()
    {
        $this->on(ControllerEvents::CREATING, function (Event\CreatingEvent $event) {
            $controller_prefix = $this->getControllerPrefix();
            $class_name = $controller_prefix . '\\' . ucfirst($event->getName()) . 'Controller';
            $controller = null;

            if (!class_exists($class_name)) {
                throw new NotFoundException('Could not find ' . $class_name);
            } else {
                $event->setClass($class_name);
            }
        });
    }

    protected function setupControllerCreateListener()
    {
        $this->on(ControllerEvents::CREATE, function (Event\CreateEvent $event) {
            $class = $event->getClass();
            $event->setInstance(new $class());
        });
    }

    protected function setupControllerDispatchListener()
    {
        $this->on(ControllerEvents::DISPATCH, function (Event\Controller\DispatchEvent $event) {
            $controller = $event->getController();
            $method = $event->getMethod();
            $url = $event->getUrl();

            call_user_func_array(array($controller, $method), $url);
        });
    }
}
