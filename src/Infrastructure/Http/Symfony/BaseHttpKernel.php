<?php

namespace Untek\Framework\Http\Infrastructure\Http\Symfony;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Untek\Core\Container\Interfaces\ContainerConfiguratorInterface;
use Untek\Core\Container\Traits\ContainerAttributeTrait;

abstract class BaseHttpKernel extends HttpKernel
{

    use ContainerAttributeTrait;

    protected string $context;
    protected string $environment;
    protected bool $debug;

    abstract protected function configureRoutes(): void;

    abstract protected function getProjectDir(): string;

    public function __construct(
        ContainerInterface $container,
        string $env,
        bool $debug,
        string $context,
        bool $handleAllThrowables = false
    ) {
        $this->setContainer($container);

        $this->environment = $env;
        $this->debug = $debug;
        $this->context = $context;

        $dispatcher = $container->get(EventDispatcherInterface::class);
        $requestStack = new RequestStack();
        $logger = $this->container->get(LoggerInterface::class);
        $resolver = new ControllerResolver($logger, $container);
        $argumentResolver = new ArgumentResolver();
        parent::__construct($dispatcher, $resolver, $requestStack, $argumentResolver, $handleAllThrowables);
    }

    public function handle(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true
    ): Response {
//        $this->bindHttp($this->container);
        $this->configureRoutes();
        $this->addRouterListener($request);
        return parent::handle($request, $type, $catch);
    }

    protected function bindHttp(ContainerInterface $container): void
    {
        $containerConfigurator = $container->get(ContainerConfiguratorInterface::class);
        $containerConfigurator->bind(UrlMatcherInterface::class, UrlMatcher::class);
        $containerConfigurator->singleton(RouteCollection::class, RouteCollection::class);
        $containerConfigurator->singleton(RequestContext::class, RequestContext::class);
        $containerConfigurator->singleton(
            RoutingConfigurator::class,
            function (ContainerInterface $container) {
                $routeCollection = $container->get(RouteCollection::class);
                $fileLocator = new FileLocator();
                $fileLoader = new PhpFileLoader($fileLocator);
                return new RoutingConfigurator($routeCollection, $fileLoader, __FILE__, __FILE__);
            }
        );
    }

    protected function addRouterListener(Request $request): void
    {
        $requestStack = $this->requestStack;
        $urlMatcher = $this->container->get(UrlMatcherInterface::class);
        $logger = $this->container->get(LoggerInterface::class);
        $requestContext = $this->container->get(RequestContext::class);
        $requestContext->fromRequest($request);
        $routerListener = new RouterListener(
            $urlMatcher,
            $requestStack,
            $requestContext,
            $logger,
            $this->getProjectDir(),
            $this->debug
        );
        $this->dispatcher->addSubscriber($routerListener);
    }

}
