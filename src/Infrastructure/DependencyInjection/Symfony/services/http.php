<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->set(UrlMatcherInterface::class, UrlMatcher::class)
        ->args(
            [
                service(RouteCollection::class),
                service(RequestContext::class),
            ]
        );
    $services->set(RouteCollection::class, RouteCollection::class);
    $services->set(RequestContext::class, RequestContext::class);

    $services->set(FileLocator::class, FileLocator::class);
    $services->set(PhpFileLoader::class, PhpFileLoader::class)
        ->args(
            [
                service(FileLocator::class)
            ]
        );
    $services->set(RoutingConfigurator::class, RoutingConfigurator::class)
        ->args(
            [
                service(RouteCollection::class),
                service(PhpFileLoader::class),
                __FILE__,
                __FILE__,
            ]
        );
};