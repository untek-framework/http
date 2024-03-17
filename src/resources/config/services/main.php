<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()->defaults()->public();

    $services->set(SessionInterface::class, Session::class);
};