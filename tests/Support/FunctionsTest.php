<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Support;

use function Linio\Common\Expressive\Support\getCurrentRouteFromRawRoutes;
use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequest;

class FunctionsTest extends TestCase
{
    public function testItGetsTheRouteConfigFromRawRoutes()
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $request = new ServerRequest([], [], '/', 'GET');

        $actual = getCurrentRouteFromRawRoutes($request, $routes);

        $this->assertSame($routes[0], $actual);
    }

    public function testItGetsTheRouteConfigFromRawRoutesWithAQueryString()
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $request = new ServerRequest([], [], '/?test=test', 'GET', 'php://input', [], [], ['test' => 'test']);

        $actual = getCurrentRouteFromRawRoutes($request, $routes);

        $this->assertSame($routes[0], $actual);
    }
}
