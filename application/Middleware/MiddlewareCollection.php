<?php

namespace Agencia\Close\Middleware;

use Agencia\Close\Middleware\Login\LoginMiddleware;
use Agencia\Close\Middleware\Login\LoginCheckMiddleware;

class MiddlewareCollection
{
    private array $middlewares = [];

    public function default()
    {
        $this->push(new LoginMiddleware());
        $this->push(new LoginCheckMiddleware());
    }

    public function run()
    {
        foreach ($this->middlewares as $middleware) {
            $middleware->run();
        }
    }

    private function push(Middleware $middleware)
    {
        array_push($this->middlewares, $middleware);
    }
}