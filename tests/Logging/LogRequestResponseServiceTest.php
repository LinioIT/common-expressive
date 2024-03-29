<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Logging;

use Linio\Common\Expressive\Filter\FilterService;
use Linio\Common\Expressive\Logging\LogRequestResponseService;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;

class LogRequestResponseServiceTest extends TestCase
{
    use ProphecyTrait;

    public function testItLogsTheRequestWithoutFilters()
    {
        $routes = require __DIR__ . '/../assets/routes.php';
        $body = [
            'test' => 'value',
        ];

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, json_encode($body));
        rewind($stream);

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->info('A request has been created.', ['body' => $body])
            ->shouldBeCalled();

        $filterService = $this->prophesize(FilterService::class);
        $filterService
            ->filter($body, [])
            ->willReturn($body);

        $getRequestLogBody = function (ServerRequestInterface $request, $body): array {
            return [
                'body' => $body,
            ];
        };

        $getResponseLogBody = function (ServerRequestInterface $request, ResponseInterface $response, $body): array {
            return [];
        };

        $request = new ServerRequest([], [], '/', 'POST', new Stream($stream));

        $logRequestResponseService = new LogRequestResponseService(
            $filterService->reveal(),
            $logger->reveal(),
            $routes,
            $getRequestLogBody,
            $getResponseLogBody
        );

        $logRequestResponseService->logRequest($request);
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

        $logger = $this->prophesize(LoggerInterface::class);
        $logger
            ->info('A response has been created.', ['body' => $body])
            ->shouldBeCalled();

        $filterService = $this->prophesize(FilterService::class);
        $filterService
            ->filter($body, [])
            ->willReturn($body);

        $getRequestLogBody = function (ServerRequestInterface $request, $body): array {
            return [
                'body' => $body,
            ];
        };

        $getResponseLogBody = function (ServerRequestInterface $request, ResponseInterface $response, $body): array {
            return [
                'body' => $body,
            ];
        };

        $request = new ServerRequest([], [], '/', 'POST', new Stream($stream));
        $response = new JsonResponse($body);

        $logRequestResponseService = new LogRequestResponseService(
            $filterService->reveal(),
            $logger->reveal(),
            $routes,
            $getRequestLogBody,
            $getResponseLogBody
        );

        $logRequestResponseService->logResponse($request, $response);
    }
}
