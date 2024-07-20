<?php

namespace Untek\Framework\Http\Infrastructure\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class HttpControllerPass implements CompilerPassInterface
{

    public function process(ContainerBuilder $container): void
    {
        $routeCollectionDefinition = $container->findDefinition(RouteCollection::class);
        $controllerServices = $container->findTaggedServiceIds('http.controller', true);
        foreach ($controllerServices as $controllerId => $tags) {
            foreach ($tags as $tag) {
                if(empty($tag['methods'])) {
                    $tag['methods'] = ['GET'];
                }
                if(empty($tag['name'])) {
                    $tag['name'] = implode('_', $tag['methods']) . '_' . trim($tag['path'], '/');
                }
                // todo: возможны проблемы при компиляции контейнера
                $route = new Route($tag['path'], ['_controller' => $controllerId]);
                $route->setMethods($tag['methods']);
                $routeCollectionDefinition->addMethodCall('add', [$tag['name'], $route]);
            }
        }
    }
}