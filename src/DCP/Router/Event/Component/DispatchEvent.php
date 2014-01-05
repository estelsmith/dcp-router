<?php

namespace DCP\Router\Event\Component;

use DCP\Router\Event\EventInterface;

class DispatchEvent implements EventInterface
{
    protected $component;

    protected $url;

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

    /**
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }
}