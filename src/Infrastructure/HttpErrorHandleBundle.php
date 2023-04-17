<?php

namespace Untek\Framework\Http\Infrastructure;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Untek\Core\Kernel\Bundle\BaseBundle;

class HttpErrorHandleBundle extends BaseBundle
{
    public function getName(): string
    {
        return 'http-error-handle';
    }

    public function build(ContainerBuilder $containerBuilder)
    {
        $this->importServices($containerBuilder, __DIR__ . '/DependencyInjection/Symfony/services/error-handle.php');
    }

    public function boot(ContainerInterface $container): void
    {
        $this->configureFromPhpFile(__DIR__ . '/DependencyInjection/Symfony/error-handler-event-dispatcher.php', $container);
    }
}
