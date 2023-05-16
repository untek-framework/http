<?php

namespace Untek\Framework\Http\Infrastructure;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Untek\Core\Kernel\Bundle\BaseBundle;

class HttpFrameworkBundle extends BaseBundle
{
    public function getName(): string
    {
        return 'http-framework';
    }

    public function build(ContainerBuilder $containerBuilder)
    {
        $this->importServices($containerBuilder, __DIR__ . '/../Resources/config/services/routing.php');
        $this->importServices($containerBuilder, __DIR__ . '/../Resources/config/services/form.php');
        $this->importServices($containerBuilder, __DIR__ . '/../Resources/config/services/main.php');
    }
}
