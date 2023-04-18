<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Logging;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Stream;
use Linio\Common\Laminas\Filter\FilterService;
use Linio\Common\Laminas\Logging\LogRequestResponseService;
use Mezzio\Router\Route;
use Mezzio\Router\RouteCollector;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Log\LoggerInterface;

class LogRequestResponseServiceTest extends TestCase
{
    use ProphecyTrait;

    public function testItLogsTheRequestWithoutFilters(): void
    {
        $routes = [];
        $routesConfig = require __DIR__ . '/../assets/routes.php';
        $middlewareInterface = $this->prophesize(MiddlewareInterface::class);

        foreach ($routesConfig as $routeConfig) {
            $routes[] = new Route($routeConfig['path'], $middlewareInterface->reveal(), $routeConfig['allowed_methods'], $routeConfig['name']);
        }

        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->getRoutes()->willReturn($routes);

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
            $routeCollector->reveal(),
            $getRequestLogBody,
            $getResponseLogBody
        );

        $logRequestResponseService->logRequest($request);
    }

    public function testItLogsTheResponseWithoutFilters(): void
    {
        $routes = [];
        $routesConfig = require __DIR__ . '/../assets/routes.php';
        $middlewareInterface = $this->prophesize(MiddlewareInterface::class);

        foreach ($routesConfig as $routeConfig) {
            $routes[] = new Route($routeConfig['path'], $middlewareInterface->reveal(), $routeConfig['allowed_methods'], $routeConfig['name']);
        }

        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->getRoutes()->willReturn($routes);

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
            $routeCollector->reveal(),
            $getRequestLogBody,
            $getResponseLogBody
        );

        $logRequestResponseService->logResponse($request, $response);
    }
}
