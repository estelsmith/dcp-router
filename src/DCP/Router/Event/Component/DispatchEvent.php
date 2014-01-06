<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router\Event\Component;

use DCP\Router\Event\EventInterface;
use DCP\Router\Exception\InvalidArgumentException;

/**
 * Event class used with ComponentEvents::DISPATCHING, ComponentEvents::DISPATCH, and ComponentEvents::DISPATCHED.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class DispatchEvent implements EventInterface
{
    /**
     * @var object|null
     */
    protected $component;

    /**
     * @var array|null
     */
    protected $url;

    /**
     * @param object $component
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setComponent($component)
    {
        if (!is_object($component)) {
            throw new InvalidArgumentException('component must be an object');
        }

        $this->component = $component;
        return $this;
    }

    /**
     * @return object|null
     */
    public function getComponent()
    {
        return $this->component;
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