<?php

declare(strict_types=1);

namespace Linio\Common\Mezzio\Middleware;

use Linio\Common\Laminas\Logging\LogRequestResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LogRequest implements MiddlewareInterface
{
    private LogRequestResponseService $loggingService;

    public function __construct(LogRequestResponseService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->loggingService->logRequest($request);

        return $handler->handle($request);
    }
}
