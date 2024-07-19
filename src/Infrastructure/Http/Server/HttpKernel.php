<?php

namespace Untek\Framework\Http\Infrastructure\Http\Server;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Throwable;
use Untek\Core\App\Bootstrap\AbstractAppKernel;
use Untek\Core\App\Bootstrap\ConfigDirectory;
use Untek\Framework\Http\Infrastructure\Http\Symfony\ControllerResolver;

class HttpKernel extends \Symfony\Component\HttpKernel\HttpKernel
{

    use MicroKernelTrait;

    protected AbstractAppKernel $kernel;
    protected ConfigDirectory $configDirectory;
    protected RouteConfigurator $routeConfigurator;

    protected string $cacheDirectory;
    protected bool $isImportLocalConfig = false;
    protected string $environment;
    protected bool $debug;
    protected string $context;
    protected bool $isTest = false;

    public function __construct(
        AbstractAppKernel $kernel,
        ConfigDirectory $configDirectory,
        string $environment,
        bool $debug,
        string $context,
        string $cacheDirectory,
        bool $isImportLocalConfig = false,
        bool $isTest = false,
        bool $handleAllThrowables = false
    )
    {
        $this->kernel = $kernel;
        $this->environment = $environment;
        $this->debug = $debug;
        $this->context = $context;
        $this->isTest = $isTest;
        $this->cacheDirectory = $cacheDirectory;
        $this->isImportLocalConfig = $isImportLocalConfig;

        $kernel->boot();
        $this->configDirectory = $configDirectory;
        $this->routeConfigurator = new RouteConfigurator($this->configDirectory, $this->context, $this->isImportLocalConfig);
        $dispatcher = $this->getContainer()->get(EventDispatcherInterface::class);
        $requestStack = new RequestStack();
        $logger = $this->getContainer()->get(LoggerInterface::class);
        $resolver = new ControllerResolver($logger, $this->getContainer());
        $argumentResolver = new ArgumentResolver();
        parent::__construct($dispatcher, $resolver, $requestStack, $argumentResolver, $handleAllThrowables);
    }

    public function getContainer(): ContainerInterface
    {
        return $this->getKernel()->getContainer();
    }

    public function handle(
        Request $request,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $catch = true
    ): Response
    {
        $routingConfigurator = $this->getContainer()->get(RoutingConfigurator::class);
        $this->routeConfigurator->configureRoutes($routingConfigurator);
        $this->addRouterListener($request);
        try {
            return parent::handle($request, $type, $catch);
        } catch (Throwable $e) {
            /** @var EventDispatcherInterface $eventDispatcher */
            $eventDispatcher = $this->getContainer()->get(EventDispatcherInterface::class);
            $event = new ExceptionEvent($this, $request, $type, $e);
            $eventDispatcher->dispatch($event, KernelEvents::EXCEPTION);
            return $event->getResponse();
        }
    }

    protected function addRouterListener(Request $request): void
    {
        $requestStack = $this->requestStack;
        $urlMatcher = $this->getContainer()->get(UrlMatcherInterface::class);
        $logger = $this->getContainer()->get(LoggerInterface::class);
        $requestContext = $this->getContainer()->get(RequestContext::class);
        $requestContext->fromRequest($request);
        $routerListener = new RouterListener(
            $urlMatcher,
            $requestStack,
            $requestContext,
            $logger,
            null, //$this->getProjectDir(),
            $this->debug
        );
        $this->dispatcher->addSubscriber($routerListener);
    }

    protected function getKernel(): AbstractAppKernel
    {
        return $this->kernel;
    }

    public function terminate(Request $request, Response $response)
    {
        parent::terminate($request, $response);
        $this->kernel->terminate();
    }
}
