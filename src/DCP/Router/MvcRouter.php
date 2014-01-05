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
    /**
     * Namespace prefix to apply to all controllers being routed to.
     * @var string
     */
    protected $controllerPrefix = '';

    public function __construct()
    {
        parent::__construct();

        $this->setupControllerCreationListeners();
        $this->setupControllerDispatchListeners();
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

    protected function setupControllerCreationListeners()
    {
        $this->on(self::EVENT_CONTROLLER_CREATING, function ($controller_name, $url) {
            $controller_prefix = $this->getControllerPrefix();
            $class_name = $controller_prefix . '\\' . ucfirst($controller_name) . 'Controller';
            $controller = null;

            if (!class_exists($class_name)) {
                throw new NotFoundException('Could not find ' . $class_name);
            } else {
                $this->emit(self::EVENT_CONTROLLER_CREATE, array($class_name, $url));
            }
        });

        $this->on(self::EVENT_CONTROLLER_CREATE, function ($class_name, $url) {
            $controller = new $class_name();
            $this->emit(self::EVENT_CONTROLLER_CREATED, array($controller, $url));
        });
    }

    protected function setupControllerDispatchListeners()
    {
        $this->on(self::EVENT_CONTROLLER_DISPATCHING, function ($controller, $url) {
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
                $this->emit(self::EVENT_CONTROLLER_DISPATCH, array($controller, $method, $url));
            }
        });

        $this->on(self::EVENT_CONTROLLER_DISPATCH, function ($controller, $method, $url) {
            $result = call_user_func_array(array($controller, $method), $url);
            $this->emit(self::EVENT_CONTROLLER_DISPATCHED, array($result, $controller, $method));
        });
    }
}
