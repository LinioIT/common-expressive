<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Logging\LogRequestResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogRequest
{
    private LogRequestResponseService $loggingService;

    public function __construct(LogRequestResponseService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $this->loggingService->logRequest($request);

        return $next($request, $response);
    }
}
