<?php

namespace Untek\Framework\Http\Infrastructure\Http\Server;

use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Untek\Core\App\Bootstrap\AbstractAppKernel;
use Untek\Core\App\Bootstrap\ConfigDirectory;

class RouteConfigurator
{

    protected AbstractAppKernel $kernel;
    protected ConfigDirectory $configDirectory;
    protected bool $isImportLocalConfig = false;
    protected string $context;

    public function __construct(
        ConfigDirectory $configDirectory,
        string $context,
        bool $isImportLocalConfig = false,
    )
    {
        $this->context = $context;
        $this->configDirectory = $configDirectory;
        $this->isImportLocalConfig = $isImportLocalConfig;
    }

    public function configureRoutes(RoutingConfigurator $routingConfigurator): void
    {
        $this->doConfigureRoutes($routingConfigurator, $this->context);
    }

    private function doConfigureRoutes(RoutingConfigurator $routes, string $context = null): void
    {
        $prefix = $this->context;
        if ($prefix == 'site') {
            $prefix = null;
        }
        $fileName = $this->configDirectory->getConfigFile('routes.php', $context);
        $this->importRoutes($routes, $fileName, $prefix);

        if ($this->isImportLocalConfig) {
            $localFileName = $this->configDirectory->getConfigFile('routes.local.php', $context);
            $this->importRoutes($routes, $localFileName, $prefix, false);
        }
    }

    private function importRoutes(RoutingConfigurator $routes, $fileName, $prefix, bool $isRequired = true): void
    {
        if ($isRequired) {
            $importConfigurator = $routes->import($fileName);
        } else {
            try {
                $importConfigurator = $routes->import($fileName);
            } catch (LoaderLoadException) {
                return;
            }
        }
        if ($prefix) {
            $importConfigurator->prefix('/' . $prefix);
        }
    }
}