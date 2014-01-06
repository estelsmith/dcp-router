<?php
/**
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
namespace DCP\Router\Event\Controller;

use DCP\Router\Event\EventInterface;
use DCP\Router\Exception\InvalidArgumentException;

/**
 * Event class used with ControllerEvents::CREATED.
 * @package dcp-router
 * @author Estel Smith <estel.smith@gmail.com>
 */
class CreatedEvent implements EventInterface
{
    /**
     * @var object|null
     */
    protected $controller;

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
}