<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Support;

use Laminas\Diactoros\ServerRequest;

use function Linio\Common\Laminas\Support\getCurrentRouteFromRawRoutes;

use PHPUnit\Framework\TestCase;

class FunctionsTest extends TestCase
{
    public function testItGetsTheRouteConfigFromRawRoutes(): void
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $request = new ServerRequest([], [], '/', 'GET');

        $actual = getCurrentRouteFromRawRoutes($request, $routes);

        $this->assertSame($routes[0], $actual);
    }

    public function testItGetsTheRouteConfigFromRawRoutesWithAQueryString(): void
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $request = new ServerRequest([], [], '/?test=test', 'GET', 'php://input', [], [], ['test' => 'test']);

        $actual = getCurrentRouteFromRawRoutes($request, $routes);

        $this->assertSame($routes[0], $actual);
    }
}
