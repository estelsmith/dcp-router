<?php

class TestRouter extends \DCP\Router\BaseRouter
{
    public $url;

    public function dispatch($url)
    {
        $this->url = $url;
    }
}