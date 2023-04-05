<?php

namespace Untek\Framework\Http\Presentation\Http\Symfony\Subscribers;

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Throwable;

class HttpHandleSubscriber implements EventSubscriberInterface
{

    private string $restApiErrorControllerClass;

    public function __construct(private ContainerInterface $container)
    {
    }

    public function setRestApiErrorControllerClass(string $restApiErrorControllerClass): void
    {
        $this->restApiErrorControllerClass = $restApiErrorControllerClass;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $request = $event->getRequest()->duplicate();
        $response = $this->forgeResponse($request, $event->getThrowable());
        $event->setResponse($response);
        $event->stopPropagation();
    }

    protected function forgeResponse(Request $request, Throwable $e): Response
    {
        $request->attributes->set('_controller', $this->restApiErrorControllerClass);
        $request->attributes->set('_action', 'handleError');
        $arguments = [
            $request,
            $e,
        ];
        $controller = $this->container->get($this->restApiErrorControllerClass);
        $response = call_user_func_array([$controller, 'handleError'], $arguments);
        return $response;
    }
}
