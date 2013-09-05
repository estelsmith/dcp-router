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

use Evenement\EventEmitterInterface;

/**
 * Provides a very minimalistic MVC-style router.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class BaseRouter implements RouterInterface {
	const EVENT_CONTROLLER_CREATE = 'dcp.router.controller.create';
	const EVENT_CONTROLLER_CREATED = 'dcp.router.controller.created';
	const EVENT_COMPONENT_CREATE = 'dcp.router.component.create';
	const EVENT_COMPONENT_CREATED = 'dcp.router.component.created';
	const EVENT_CONTROLLER_DISPATCH = 'dcp.router.controller.dispatch';
	const EVENT_CONTROLLER_DISPATCHED = 'dcp.router.controller.dispatched';
	const EVENT_COMPONENT_DISPATCH = 'dcp.router.component.dispatch';
	const EVENT_COMPONENT_DISPATCHED = 'dcp.router.component.dispatched';

	/**
	 * The event emitter we use to control actual routing work.
	 * @var \Evenement\EventEmitterInterface
	*/
	protected $_emitter;

	/**
	 * Listing of components that the router may route to.
	 * @var array
	*/
	protected $_components = array();

	/**
	 * Namespace prefix to apply to all controllers being routed to.
	 * @var string
	*/
	protected $_controllerPrefix = '';

	/**
	 * @param array $components Array of components to attach to the router.
	 * @param string $controller_prefix Namespace prefix to apply to all controllers being routed to.
	*/
	public function __construct(EventEmitterInterface $emitter, $components = NULL, $controller_prefix = NULL) {
		$this->_emitter = $emitter;

		if ($components) {
			$this->setComponents($components);
		}

		if ($controller_prefix) {
			$this->setControllerPrefix($controller_prefix);
		}

		$controller_dispatch = self::EVENT_CONTROLLER_DISPATCH;
		$emitter->on(self::EVENT_CONTROLLER_CREATED, function($controller, $url) use($emitter, $controller_dispatch) {
			$emitter->emit($controller_dispatch, array($controller, $url));
		});

		$component_dispatch = self::EVENT_COMPONENT_DISPATCH;
		$emitter->on(self::EVENT_COMPONENT_CREATED, function($component, $url) use($emitter, $component_dispatch) {
			$emitter->emit($component_dispatch, array($component, $url));
		});
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
		$emitter = $this->_emitter;

		if (!is_array($url)) {
			$url = $this->_convertUrlToArray($url);
		}

		/*
		 * Route to the index controller if no route was presented.
		 *
		 * Otherwise, route to an appropriate component, if it exists, or route to the appropriate
		 * controller if there is no acceptable component to route to.
		*/
		if (!$url) {
			$emitter->emit(self::EVENT_CONTROLLER_CREATE, array('index', array()));
		} else {
			$node = array_shift($url);

			if (isset($this->_components[$node])) {
				$emitter->emit(self::EVENT_COMPONENT_CREATE, array($this->_components[$node], $url));
			} else {
				$emitter->emit(self::EVENT_CONTROLLER_CREATE, array($node, $url));
			}
		}

		return $return_value;
	}
}