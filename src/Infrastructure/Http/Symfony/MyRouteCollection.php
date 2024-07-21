<?php

namespace Untek\Framework\Http\Infrastructure\Http\Symfony;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class MyRouteCollection extends RouteCollection
{

    public function addRoute($controllerId, string $path, string $name, array $methods): void
    {
        $route = new Route($path, ['_controller' => $controllerId]);
        $route->setMethods($methods);
        $this->add($name, $route);
    }
}
