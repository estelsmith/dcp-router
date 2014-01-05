<?php

namespace DCP\Router\Event\Component;

use DCP\Router\Event\EventInterface;

class CreatedEvent implements EventInterface
{
    protected $component;

    /**
     * @param $component
     * @return $this
     */
    public function setComponent($component)
    {
        $this->component = $component;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComponent()
    {
        return $this->component;
    }
}