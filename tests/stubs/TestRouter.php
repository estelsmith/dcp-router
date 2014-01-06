<?php

class TestRouter extends \DCP\Router\BaseRouter
{
    public $url;

    public $method;

    public function dispatch($url, $method = null)
    {
        $this->url = $url;
        $this->method = $method;
    }
}