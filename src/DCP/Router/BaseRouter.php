<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

use DCP\Router\Exception\InvalidArgumentException;
use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use DCP\Router\Exception\NotFoundException;

/**
 * Provides a minimalistic event-based router platform.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
abstract class BaseRouter implements BaseRouterInterface, EventEmitterInterface
{
    use EventEmitterTrait;

    /**
     * List of other routers that the router may dispatch.
     * @var array
    */
    protected $components = [];

    /**
     * Namespace prefix applied to all resolved controllers.
     * @var string
     */
    protected $controllerPrefix = '';

    public function __construct()
    {
        $this->setupControllerListeners();
        $this->setupComponentListeners();
    }

    /**
     * {@inheritdoc}
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * {@inheritdoc}
     */
    public function setComponents($components)
    {
        if (!is_array($components)) {
            throw new InvalidArgumentException('components must be an array');
        }

        $this->components = $components;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerPrefix()
    {
        return $this->controllerPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function setControllerPrefix($prefix)
    {
        if (!is_string($prefix)) {
            throw new InvalidArgumentException('prefix must be a string');
        }

        $this->controllerPrefix = $prefix;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($url)
    {
        if (!is_string($url) && !is_array($url)) {
            throw new InvalidArgumentException('url must be a string or array');
        }

        if (!is_array($url)) {
            $url = $this->convertUrlToArray($url);
        }

        if (!$url) {
            $this->dispatchController('index', []);
        } else {
            $node = array_shift($url);

            if (isset($this->components[$node])) {
                $this->dispatchComponent($node, $url);
            } else {
                $this->dispatchController($node, $url);
            }
        }
    }

    /**
     * Converts a string-based URL into an array appropriate for consuming by the router.
     * @param string $url
     * @return array|bool Returns an array on success, or FALSE on failure.
     */
    protected function convertUrlToArray($url)
    {
        $return_value = false;

        if ($url) {
            // Remove the query string if it exists.
            $url = explode('?', $url);
            $url = $url[0];

            // Break URL into an array.
            $url = explode('/', $url);

            // Loop through the array and remove any empty entries caused by consecutive slashes.
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

    /**
     * Add default event listeners for component creation/dispatch.
     */
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

    /**
     * Dispatch current URL to the given component router.
     * @param string $node
     * @param array $url
     */
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

    /**
     * Add default event listeners for controller creation/dispatch.
     */
    protected function setupControllerListeners()
    {
        $this->setupControllerCreatingListener();
        $this->setupControllerCreateListener();
        $this->setupControllerDispatchListener();
    }

    /**
     * Add default event listener for ControllerEvents::CREATING event.
     */
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

    /**
     * Add default event listener for ControllerEvents::CREATE event.
     */
    protected function setupControllerCreateListener()
    {
        $this->on(ControllerEvents::CREATE, function (Event\CreateEvent $event) {
            $class = $event->getClass();
            $event->setInstance(new $class());
        });
    }

    /**
     * Add default event listener for ControllerEvents::DISPATCH event.
     */
    protected function setupControllerDispatchListener()
    {
        $this->on(ControllerEvents::DISPATCH, function (Event\Controller\DispatchEvent $event) {
            $controller = $event->getController();
            $method = $event->getMethod();
            $url = $event->getUrl();

            call_user_func_array(array($controller, $method), $url);
        });
    }

    /**
     * Dispatch currently URL to an application controller.
     * @param string $node
     * @param string $url
     */
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
}
