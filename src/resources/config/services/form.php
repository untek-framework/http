<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Form\ResolvedFormTypeFactoryInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()->defaults()->public();

    $services->set(ResolvedFormTypeFactoryInterface::class, ResolvedFormTypeFactory::class);
    $services->set(HttpFoundationExtension::class, HttpFoundationExtension::class);
    $services->set(FormRegistryInterface::class, FormRegistry::class)
        ->args(
            [
                [
                    service(HttpFoundationExtension::class)
                ],
                service(ResolvedFormTypeFactoryInterface::class),
            ]
        );
    $services->set(FormFactoryInterface::class, FormFactory::class)
        ->args(
            [
                service(FormRegistryInterface::class),
            ]
        );
    $services->set(CsrfTokenManagerInterface::class, CsrfTokenManager::class);
};