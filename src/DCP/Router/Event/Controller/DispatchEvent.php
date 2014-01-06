<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router\Event\Controller;

use DCP\Router\Event\EventInterface;
use DCP\Router\Exception\InvalidArgumentException;

/**
 * Event class used with ControllerEvents::DISPATCHING, ControllerEvents::DISPATCH, and ControllerEvents::DISPATCHED.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class DispatchEvent implements EventInterface
{
    /**
     * @var object|null
     */
    protected $controller;

    /**
     * @var string|null
     */
    protected $method;

    /**
     * @var array|null
     */
    protected $url;

    /**
     * @param object $controller
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setController($controller)
    {
        if (!is_object($controller)) {
            throw new InvalidArgumentException('controller must be an object');
        }

        $this->controller = $controller;
        return $this;
    }

    /**
     * @return object|null
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string $method
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setMethod($method)
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException('method must be a string');
        }

        $this->method = $method;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param array $url
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setUrl($url)
    {
        if (!is_array($url)) {
            throw new InvalidArgumentException('url must be an array');
        }

        $this->url = $url;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getUrl()
    {
        return $this->url;
    }
}
