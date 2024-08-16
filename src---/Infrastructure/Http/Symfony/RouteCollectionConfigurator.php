<?php

namespace Untek\Framework\Http\Infrastructure\Http\Symfony;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteCollectionConfigurator
{

    public function __construct(private RouteCollection $routes)
    {
    }

    public function add($controllerId, string $path, string $name, array $methods): void
    {
        $route = new Route($path, ['_controller' => $controllerId]);
        $route->setMethods($methods);
        $this->routes->add($name, $route);
    }
}
