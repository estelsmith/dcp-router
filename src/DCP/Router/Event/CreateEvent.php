<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router\Event;
use DCP\Router\Exception\InvalidArgumentException;

/**
 * Event class used with ComponentEvents::CREATE and ControllerEvents::CREATE.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class CreateEvent implements EventInterface
{
    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var object|null
     */
    protected $instance;

    /**
     * @param string $class
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setClass($class)
    {
        if (!is_string($class)) {
            throw new InvalidArgumentException('class must be a string');
        }

        $this->class = $class;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param object $instance
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setInstance($instance)
    {
        if (!is_object($instance)) {
            throw new InvalidArgumentException('instance must be an object');
        }

        $this->instance = $instance;
        return $this;
    }

    /**
     * @return object|null
     */
    public function getInstance()
    {
        return $this->instance;
    }
}