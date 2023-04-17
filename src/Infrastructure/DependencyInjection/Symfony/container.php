<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

$containerBuilder = new ContainerBuilder();

$fileLocator = new FileLocator(__DIR__);
$loader = new PhpFileLoader($containerBuilder, $fileLocator);

$loader->load(__DIR__ . '/services/routing.php');
$loader->load(__DIR__ . '/services/form.php');

return $containerBuilder;
