<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;

/**
 * Provides a very minimalistic MVC-style router.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class BaseRouter implements BaseRouterInterface, EventEmitterInterface
{
    use EventEmitterTrait;

    const EVENT_CONTROLLER_CREATE = 'dcp.router.controller.create';
    const EVENT_CONTROLLER_CREATED = 'dcp.router.controller.created';
    const EVENT_COMPONENT_CREATE = 'dcp.router.component.create';
    const EVENT_COMPONENT_CREATED = 'dcp.router.component.created';
    const EVENT_CONTROLLER_DISPATCH = 'dcp.router.controller.dispatch';
    const EVENT_CONTROLLER_DISPATCHED = 'dcp.router.controller.dispatched';
    const EVENT_COMPONENT_DISPATCH = 'dcp.router.component.dispatch';
    const EVENT_COMPONENT_DISPATCHED = 'dcp.router.component.dispatched';

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
        $this->on(self::EVENT_CONTROLLER_CREATED, function ($controller, $url) {
            $this->emit(self::EVENT_CONTROLLER_DISPATCH, array($controller, $url));
        });

        $this->on(self::EVENT_COMPONENT_CREATED, function ($component, $url) {
            $this->emit(self::EVENT_COMPONENT_DISPATCH, array($component, $url));
        });
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
            $this->emit(self::EVENT_CONTROLLER_CREATE, array('index', array()));
        } else {
            $node = array_shift($url);

            if (isset($this->components[$node])) {
                $this->emit(self::EVENT_COMPONENT_CREATE, array($this->components[$node], $url));
            } else {
                $this->emit(self::EVENT_CONTROLLER_CREATE, array($node, $url));
            }
        }

        return $return_value;
    }
}
