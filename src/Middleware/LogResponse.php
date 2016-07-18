<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Common\Expressive\Logging\LogRequestResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogResponse
{
    /**
     * @var LogRequestResponseService
     */
    private $loggingService;

    /**
     * @param LogRequestResponseService $loggingService
     */
    public function __construct(LogRequestResponseService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $response = $next($request, $response);

        $this->loggingService->logResponse($request, $response);

        return $response;
    }
}
