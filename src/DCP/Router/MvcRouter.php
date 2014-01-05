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
class MvcRouter extends BaseRouter implements MvcRouterInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->setupControllerListeners();
        $this->setupComponentListeners();
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

    protected function setupControllerListeners()
    {
        $this->on(self::EVENT_CONTROLLER_CREATE, function ($controller_name, $url) {
            $controller_prefix = $this->getControllerPrefix();
            $class_name = $controller_prefix . '\\' . ucfirst($controller_name) . 'Controller';
            $controller = null;

            if (!class_exists($class_name)) {
                throw new NotFoundException('Could not find ' . $class_name);
            } else {
                $controller = new $class_name();
                $this->emit(self::EVENT_CONTROLLER_CREATED, array($controller, $url));
            }
        });

        $this->on(self::EVENT_CONTROLLER_DISPATCH, function ($controller, $url) {
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
                $this->emit(self::EVENT_CONTROLLER_DISPATCHED, array($result, $controller, $method));
            }
        });
    }

    protected function setupComponentListeners()
    {
        $this->on(self::EVENT_COMPONENT_CREATE, function ($component_name, $url) {
            $component = new $component_name();
            $this->emit(self::EVENT_COMPONENT_CREATED, array($component, $url));
        });

        $this->on(self::EVENT_COMPONENT_DISPATCH, function ($component, $url) {
            $result = $component->dispatch($url);
            $this->emit(self::EVENT_COMPONENT_DISPATCHED, array($result, $component, $url));
        });
    }
}
