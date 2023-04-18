<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Exception\Http\ContentTypeNotSupportedException;
use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Middleware\ValidateSupportedContentTypes;
use Linio\TestAssets\TestMiddleware;
use Mezzio\Router\Route;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateSupportedContentTypesTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider unsupportedContentTypeRequestProvider
     */
    public function testItOnlyAllowsSupportedContentTypes(ServerRequestInterface $request): void
    {
        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->getRoutes()->willReturn([]);

        $this->expectException(ContentTypeNotSupportedException::class);

        $middleware = new ValidateSupportedContentTypes([], $routeCollector->reveal());
        $middleware->process($request, $handler->reveal());
    }

    public function testItUsesRouteSpecificOverrides(): void
    {
        $routes = [];
        $routesConfig = require __DIR__ . '/../assets/routes.php';
        $middlewareInterface = $this->prophesize(MiddlewareInterface::class);

        foreach ($routesConfig as $routeConfig) {
            $route = new Route(
                $routeConfig['path'],
                $middlewareInterface->reveal(),
                $routeConfig['allowed_methods'],
                $routeConfig['name']
            );

            $route->setOptions(['validation_rules' => $routeConfig['validation_rules']]);
            $routes[] = $route;
        }

        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->getRoutes()->willReturn($routes);

        $route = new Route('test_valid_content_type', new TestMiddleware(), ['GET'], 'test_valid_content_type');
        $request = (new ServerRequest([], [], '/valid-content-type'))
            ->withHeader('Content-Type', 'supported')
            ->withAttribute(RouteResult::class, RouteResult::fromRoute($route));

        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $middleware = new ValidateSupportedContentTypes([], $routeCollector->reveal());
        $actual = $middleware->process($request, $handler->reveal());

        $this->assertSame($response, $actual);
    }

    public function testItAllowsNoContentTypesForStandardPages(): void
    {
        $routes = [];
        $routesConfig = require __DIR__ . '/../assets/routes.php';
        $middlewareInterface = $this->prophesize(MiddlewareInterface::class);

        foreach ($routesConfig as $routeConfig) {
            $route = new Route(
                $routeConfig['path'],
                $middlewareInterface->reveal(),
                $routeConfig['allowed_methods'],
                $routeConfig['name']
            );

            $options = [
                'validation_rules' => $routeConfig['validation_rules'] ?? null,
                'content_type' => $routeConfig['content_types'] ?? null,
            ];

            $route->setOptions($options);
            $routes[] = $route;
        }

        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->getRoutes()->willReturn($routes);

        $route = new Route('/no-content-type', new TestMiddleware(), ['GET'], 'test_no_content_type');
        $request = (new ServerRequest())
            ->withAttribute(RouteResult::class, RouteResult::fromRoute($route));
        $response = new Response();

        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response)->shouldBeCalled();

        $middleware = new ValidateSupportedContentTypes([], $routeCollector->reveal());
        $middleware->supportType(null);
        $middleware->process($request, $handler->reveal());
    }

    public function testItRequiresTheRouterMiddlewareToHaveBeenRun(): void
    {
        $routes = [];
        $routesConfig = require __DIR__ . '/../assets/routes.php';
        $middlewareInterface = $this->prophesize(MiddlewareInterface::class);

        foreach ($routesConfig as $routeConfig) {
            $route = new Route(
                $routeConfig['path'],
                $middlewareInterface->reveal(),
                $routeConfig['allowed_methods'],
                $routeConfig['name']
            );

            $route->setOptions(['validation_rules' => $routeConfig['validation_rules']]);
            $routes[] = $route;
        }

        $routeCollector = $this->prophesize(RouteCollector::class);
        $routeCollector->getRoutes()->willReturn($routes);

        $request = new ServerRequest();
        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new ValidateSupportedContentTypes([], $routeCollector->reveal());
        $middleware->process($request, $handler->reveal());
    }

    public function unsupportedContentTypeRequestProvider(): array
    {
        $route = new Route('test', new TestMiddleware(), ['GET'], 'test_valid_content_type');
        $routeResult = RouteResult::fromRoute($route);

        return [
            [(new ServerRequest())->withAttribute(RouteResult::class, $routeResult)],
            [(new ServerRequest())->withHeader('Content-Type', 'unsupported')->withAttribute(RouteResult::class, $routeResult)],
        ];
    }
}
