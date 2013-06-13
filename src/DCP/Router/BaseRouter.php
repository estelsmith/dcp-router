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
use DCP\Router\Exception\InvalidArgumentException;

/**
 * Provides a very minimalistic MVC-style router.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class BaseRouter {
	/**
	 * Listing of components that the router may route to.
	 * @var array
	*/
	protected $_components = array();

	/**
	 * Callback to be executed any time a component needs routed to.
	 * @var callable
	*/
	protected $_componentCallback;

	/**
	 * Callback to be executed any time a controller needs routed to.
	 * @var callable
	*/
	protected $_controllerCallback;

	/**
	 * Namespace prefix to apply to all controllers being routed to.
	 * @var string
	*/
	protected $_controllerPrefix = __NAMESPACE__;

	/**
	 * @param array $components Array of components to attach to the router.
	 * @param string $controller_prefix Namespace prefix to apply to all controllers being routed to.
	*/
	public function __construct($components = NULL, $controller_prefix = NULL) {
		if ($components) {
			$this->setComponents($components);
		}

		if ($controller_prefix) {
			$this->setControllerPrefix($controller_prefix);
		}

		$this->setComponentCallback($this->getDefaultComponentCallback());
		$this->setControllerCallback($this->getDefaultControllerCallback());
	}

	/**
	 * Retrieve the components that have been attached to the router.
	 * @return array
	*/
	public function getComponents() {
		return $this->_components;
	}

	/**
	 * Attach an array of components to the router.
	 * @param array $components
	*/
	public function setComponents($components) {
		$this->_components = $components;
	}

	/**
	 * Retrieve the namespace prefix being applied to controllers being routed to.
	 * @return string
	*/
	public function getControllerPrefix() {
		return $this->_controllerPrefix;
	}

	/**
	 * Set the namespace prefix to apply to all controllers being routed to.
	 * @param string $prefix
	*/
	public function setControllerPrefix($prefix) {
		$this->_controllerPrefix = $prefix;
	}

	/**
	 * Returns a callable that provides default functionality for routing to components.
	 * @return callable
	*/
	public function getDefaultComponentCallback() {
		return function($node, $url) {
			// Simply route to the next component.
			$instance = new $node();
			return $instance->dispatch($url);
		};
	}

	/**
	 * Set a new callback to be executed when the router determines it needs to route to a component.
	 * @param callable $callback
	*/
	public function setComponentCallback($callback) {
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Argument is not callable.');
		} else {
			$this->_componentCallback = $callback;
		}
	}

	/**
	 * Returns a callable that provides default functionality for routing to controllers.
	 * @return callable
	*/
	public function getDefaultControllerCallback() {
		$router = $this;

		return function($node, $url) use($router) {
			$return_value = FALSE;

			// Build the class name of the controller we're getting ready to instantiate.
			$class_name = $router->getControllerPrefix() . '\\' . ucfirst($node) . 'Controller';

			// Set the default action, in case no action was specified in the URL.
			$method = 'index';

			// Get action from URL if it exists.
			if (count($url) > 0) {
				$method = array_shift($url);
			}

			$method .= 'Action';

			/*
			 * Instantiate the controller class, and call the action method that was defined.
			 * Throw a NotFoundException if either the class or method does not exist.
			*/
			if (!class_exists($class_name)) {
				throw new NotFoundException('Could not find ' . $class_name);
			} else {
				$instance = new $class_name();

				if (!method_exists($instance, $method)) {
					throw new NotFoundException('Could not find ' . $class_name . '::' . $method);
				} else {
					$return_value = call_user_func_array(array($instance, $method), $url);
				}
			}

			return $return_value;
		};
	}

	/**
	 * Set a new callback to be executed when the router determines it needs to router to a controller.
	 * @param callable $callback
	*/
	public function setControllerCallback($callback) {
		if (!is_callable($callback)) {
			throw new InvalidArgumentException('Argument is not callable.');
		} else {
			$this->_controllerCallback = $callback;
		}
	}

	/**
	 * Converts a URL to an array for routing purposes. Returns the array on success, or FALSE on failure.
	 * @return array|false
	*/
	protected function _convertUrlToArray($url) {
		$return_value = FALSE;

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
	public function dispatch($url) {
		$return_value = FALSE;

		if (!is_array($url)) {
			$url = $this->_convertUrlToArray($url);
		}

		/*
		 * Route to the index controller if no route was presented.
		 *
		 * Otherwise, route to an appropriate component, if it exists, or route to the appropriate controller
		 * if there is no acceptable component to route to.
		*/
		if (!$url) {
			$return_value = call_user_func_array($this->_controllerCallback, array('index', array()));
		} else {
			$node = array_shift($url);

			if (isset($this->_components[$node])) {
				$return_value = call_user_func_array($this->_componentCallback, array($this->_components[$node], $url));
			} else {
				$return_value = call_user_func_array($this->_controllerCallback, array($node, $url));
			}
		}

		return $return_value;
	}
}