<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Untek\Component\Web\Layout\Subscribers\SetLayoutSubscriber;
use Untek\Component\Web\WebApp\Subscribers\WebAuthenticationSubscriber;
use Untek\Framework\Http\Presentation\Http\Symfony\Controllers\HttpErrorController;
use Untek\Framework\Http\Presentation\Http\Symfony\Subscribers\HttpHandleSubscriber;

return function (EventDispatcherInterface $eventDispatcher, ContainerInterface $container) {
    $webAuthenticationSubscriber = $container->get(WebAuthenticationSubscriber::class);
    $eventDispatcher->addSubscriber($webAuthenticationSubscriber);

    /** @var HttpHandleSubscriber $restApiHandleSubscriber */
    $restApiHandleSubscriber = $container->get(HttpHandleSubscriber::class);
    $restApiHandleSubscriber->setRestApiErrorControllerClass(HttpErrorController::class);
    $eventDispatcher->addSubscriber($restApiHandleSubscriber);
};
