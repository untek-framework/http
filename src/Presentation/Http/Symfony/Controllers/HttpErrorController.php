<?php

namespace Untek\Framework\Http\Presentation\Http\Symfony\Controllers;

use axy\backtrace\helpers\Represent;
use axy\backtrace\Trace;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Untek\Core\Contract\Common\Exceptions\InvalidConfigException;
use Untek\Core\Contract\Common\Exceptions\NotFoundException;
use Untek\Core\Env\Helpers\EnvHelper;

class HttpErrorController
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handleError(Request $request, Exception $exception): Response
    {
        $data = [
            'attributes' => $request->attributes->all(),
            'request' => $request->request->all(),
            'query' => $request->query->all(),
            'server' => $request->server->all(),
            'files' => $request->files->all(),
            'cookies' => $request->cookies->all(),
            'headers' => $request->headers->all(),
            'requestUri' => $request->getRequestUri(),
            'method' => $request->getMethod(),
        ];
        $logMessage = $exception->getMessage() ?: get_class($exception);
        $this->logger->error(
            $logMessage,
            [
                'request' => $data,
                'trace' => debug_backtrace()
            ]
        );
        if ($exception instanceof AccessDeniedException) {
            return $this->forbidden($request, $exception);
        }
        if ($exception instanceof AuthenticationException) {
            return $this->unauthorized($request, $exception);
        }
        if ($exception instanceof NotFoundException) {
            return $this->notFound($request, $exception);
        }
        if ($exception instanceof ResourceNotFoundException) {
            return $this->notFound($request, $exception);
        }
        if ($exception instanceof NotFoundHttpException) {
            return $this->notFound($request, $exception);
        }
        if ($exception instanceof InvalidConfigException) {
            return $this->commonRender('Config error', $exception->getMessage(), $exception);
        }
        return $this->commonRender('Error!', $exception->getMessage(), $exception);
    }

    protected function commonRender(
        string $title,
        string $message,
        \Throwable $exception,
        int $statusCode = 500
    ): Response {
        $params = [
            'title' => $title,
            'message' => $message,
        ];
        if (EnvHelper::isDebug()) {
            $params['exception'] = $exception;
        }

        $content = "<h1>{$params['title']}</h1>";
        $content .= "
            <div class=\"alert alert-danger\" role=\"alert\">
              {$params['message']}
            </div>";

        if (getenv('APP_DEBUG')) {
            $trace = new Trace($exception->getTrace());
            $trace->trimFilename(getenv('ROOT_DIRECTORY'));
            $traceContent = (Represent::trace($trace->getItems()));
            $content .= "
                <div class=\"alert alert-secondary\" role=\"alert\">
                  <code><pre>{$traceContent}</pre></code>
                </div>";
        }

        return new Response($content, $statusCode);
    }

    private function notFound(Request $request, Exception $exception): Response
    {
        return $this->commonRender('Not found', 'Page not exists!', $exception, 404);
    }

    private function unauthorized(Request $request, Exception $exception): Response
    {
        return $this->commonRender('Unauthorized', 'Unauthorized', $exception, 401);
    }

    private function forbidden(Request $request, Exception $exception): Response
    {
        return $this->commonRender('Forbidden', 'Access error', $exception, 403);
    }
}
