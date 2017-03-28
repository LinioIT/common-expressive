<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Linio\Common\Expressive\Logging\LogRequestResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogRequest implements MiddlewareInterface
{
    /**
     * @var LogRequestResponseService
     */
    private $loggingService;

    public function __construct(LogRequestResponseService $loggingService)
    {
        $this->loggingService = $loggingService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $this->loggingService->logRequest($request);

        return $delegate->process($request);
    }
}
