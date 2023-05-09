<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Support;

use Laminas\Diactoros\ServerRequest;

use function Linio\Common\Laminas\Support\getCurrentRouteFromRawRoutes;

use Mezzio\Router\Route;
use Mezzio\Router\RouteCollector;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\MiddlewareInterface;

class FunctionsTest extends TestCase
{
    use ProphecyTrait;

    public function testItGetsTheRouteConfigFromRawRoutes(): void
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

        $request = new ServerRequest([], [], '/', 'GET');

        $actual = getCurrentRouteFromRawRoutes($request, $routeCollector->reveal());

        $this->assertSame($routes[0], $actual);
    }

    public function testItGetsTheRouteConfigFromRawRoutesWithAQueryString(): void
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

        $request = new ServerRequest([], [], '/?test=test', 'GET', 'php://input', [], [], ['test' => 'test']);

        $actual = getCurrentRouteFromRawRoutes($request, $routeCollector->reveal());

        $this->assertSame($routes[0], $actual);
    }
}
