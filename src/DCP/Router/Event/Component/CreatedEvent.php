<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router\Event\Component;

use DCP\Router\Event\EventInterface;
use DCP\Router\Exception\InvalidArgumentException;

/**
 * Event class used with ComponentEvents::CREATED.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class CreatedEvent implements EventInterface
{
    /**
     * @var object|null
     */
    protected $component;

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
}
