<?php

namespace stubs\Controller;

class TestController
{
    public $arg;

    public function testAction($arg = null)
    {
        $this->arg = $arg;
    }

    public function get($arg = null)
    {
        throw new \Exception($arg);
    }
}