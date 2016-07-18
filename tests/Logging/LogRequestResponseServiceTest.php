<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Logging;

use Eloquent\Phony\Phpunit\Phony;
use Linio\Common\Expressive\Filter\FilterService;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

class LogRequestResponseServiceTest extends TestCase
{
    public function testItLogsTheRequestWithoutFilters()
    {
        $routes = require __DIR__ . '/../assets/routes.php';
        $body = [
            'test' => 'value',
        ];

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, json_encode($body));
        rewind($stream);

        $logger = Phony::mock(LoggerInterface::class);

        $filterService = Phony::mock(FilterService::class);
        $filterService->filter->with($body, [])->returns($body);

        $getRequestLogBody = Phony::spy(function (ServerRequestInterface $request, $body): array {
            return [
                'body' => $body,
            ];
        });

        $getResponseLogBody = function (ServerRequestInterface $request, ResponseInterface $response, $body): array {
            return [];
        };

        $request = new ServerRequest([], [], '/', 'POST', new Stream($stream));

        $logRequestResponseService = new LogRequestResponseService(
            $filterService->get(),
            $logger->get(),
            $routes,
            $getRequestLogBody,
            $getResponseLogBody
        );

        $logRequestResponseService->logRequest($request);

        $logger->info->calledWith('A request has been created.', ['body' => $body]);
    }

    public function testItLogsTheResponseWithoutFilters()
    {
        $routes = require __DIR__ . '/../assets/routes.php';
        $body = [
            'test' => 'value',
        ];

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, json_encode($body));
        rewind($stream);

        $logger = Phony::mock(LoggerInterface::class);

        $filterService = Phony::mock(FilterService::class);
        $filterService->filter->with($body, [])->returns($body);

        $getRequestLogBody = Phony::spy(function (ServerRequestInterface $request, $body): array {
            return [
                'body' => $body,
            ];
        });

        $getResponseLogBody = function (ServerRequestInterface $request, ResponseInterface $response, $body): array {
            return [
                'body' => $body,
            ];
        };

        $request = new ServerRequest([], [], '/', 'POST', new Stream($stream));
        $response = new JsonResponse($body);

        $logRequestResponseService = new LogRequestResponseService(
            $filterService->get(),
            $logger->get(),
            $routes,
            $getRequestLogBody,
            $getResponseLogBody
        );

        $logRequestResponseService->logResponse($request, $response);

        $logger->info->calledWith('A response has been created.', ['body' => $body]);
    }
}
