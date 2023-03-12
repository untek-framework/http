<?php

namespace Untek\Framework\Http\Infrastructure\Http\Symfony;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ControllerResolver extends \Symfony\Component\HttpKernel\Controller\ControllerResolver
{

    public function __construct(LoggerInterface $logger = null, private ContainerInterface $container)
    {
        parent::__construct($logger);
    }

    /**
     * Returns an instantiated controller.
     */
    protected function instantiateController(string $class): object
    {
        return $this->container->get($class);
    }
}
