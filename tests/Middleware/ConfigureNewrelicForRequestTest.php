<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Eloquent\Phony\Phpunit\Phony;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Middleware\ConfigureNewrelicForRequest;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\RequestHandlerInterface;

class ConfigureNewrelicForRequestTest extends TestCase
{
    use ProphecyTrait;

    public function setUp(): void
    {
        if (!extension_loaded('newrelic')) {
            require_once __DIR__ . '/../assets/NewrelicFunctions.php';
        }

        parent::setUp();
    }

    public function testItDoesNothingIfNewrelicIsNotInstalled(): void
    {
        $namespace = 'Linio\Common\Laminas\Middleware';

        $request = new ServerRequest();
        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        Phony::stubGlobal('extension_loaded', $namespace)->returns(false);
        $setAppName = Phony::stubGlobal('newrelic_set_appname', $namespace);
        $nameTransaction = Phony::stubGlobal('newrelic_name_Transaction', $namespace);
        $addRequestId = Phony::stubGlobal('newrelic_add_custom_parameter', $namespace);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->process($request, $handler->reveal());

        $setAppName->never()->called();
        $nameTransaction->never()->called();
        $addRequestId->never()->called();
    }

    public function testItSetsTheAppName(): void
    {
        $namespace = 'Linio\Common\Laminas\Middleware';

        $appName = 'testApp';
        $routeName = 'testRoute';
        $requestId = '1000';

        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult
            ->isFailure()
            ->willReturn(false);
        $routeResult
            ->getMatchedRouteName()
            ->willReturn($routeName);

        $request = (new ServerRequest())
            ->withAttribute(RouteResult::class, $routeResult->reveal())
            ->withAttribute('requestId', $requestId);

        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        Phony::stubGlobal('extension_loaded', $namespace)->returns(true);

        $setAppName = Phony::stubGlobal('newrelic_set_appname', $namespace);

        $middleware = new ConfigureNewrelicForRequest($appName);
        $middleware->process($request, $handler->reveal());

        $setAppName->calledWith($appName);
    }

    public function testItAddsARequestIdParameter(): void
    {
        $namespace = 'Linio\Common\Laminas\Middleware';

        $requestId = '1000';

        $request = (new ServerRequest())->withAttribute('requestId', $requestId);
        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        Phony::stubGlobal('extension_loaded', $namespace)->returns(true);
        $addRequestId = Phony::stubGlobal('newrelic_add_custom_parameter', $namespace);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->process($request, $handler->reveal());

        $addRequestId->calledWith('requestId', $requestId);
    }

    public function testItDoesntAddARequestIdWhenOneDoesntExist(): void
    {
        $namespace = 'Linio\Common\Laminas\Middleware';

        $request = new ServerRequest();
        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        Phony::stubGlobal('extension_loaded', $namespace)->returns(true);
        $addRequestId = Phony::stubGlobal('newrelic_add_custom_parameter', $namespace);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->process($request, $handler->reveal());

        $addRequestId->never()->called();
    }

    public function testItNamesTheTransaction(): void
    {
        $namespace = 'Linio\Common\Laminas\Middleware';

        $routeName = 'testRoute';

        $routeResult = $this->prophesize(RouteResult::class);
        $routeResult
            ->isFailure()
            ->willReturn(false);
        $routeResult
            ->getMatchedRouteName()
            ->willReturn($routeName);

        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult->reveal());
        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        Phony::stubGlobal('extension_loaded', $namespace)->returns(true);
        $nameTransaction = Phony::stubGlobal('newrelic_name_transaction', $namespace);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->process($request, $handler->reveal());

        $nameTransaction->calledWith($routeName);
    }
}
