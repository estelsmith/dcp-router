<?php

namespace DCP\Router\Event\Controller;

use DCP\Router\Event\EventInterface;

class CreatedEvent implements EventInterface
{
    protected $controller;

    /**
     * @param $controller
     * @return $this
     */
    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }
}