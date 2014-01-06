<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router\Event;
use DCP\Router\Exception\InvalidArgumentException;

/**
 * Event class used with ComponentEvents::CREATING and ControllerEvents::CREATING.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class CreatingEvent implements EventInterface
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $class = '';

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
     * @param string $name
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setName($name)
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('name must be a string');
        }

        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}