<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Untek\Framework\Http\Presentation\Http\Symfony\Controllers\HttpErrorController;
use Untek\Framework\Http\Presentation\Http\Symfony\Subscribers\HttpHandleSubscriber;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->set(HttpErrorController::class, HttpErrorController::class)
        ->args(
            [
                service(LoggerInterface::class)
            ]
        );
    $services->set(HttpHandleSubscriber::class, HttpHandleSubscriber::class)
        ->args(
            [
                service(ContainerInterface::class),
            ]
        );
};