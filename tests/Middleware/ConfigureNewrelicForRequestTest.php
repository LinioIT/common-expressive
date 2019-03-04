<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Eloquent\Phony\Phony;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\RouteResult;

class ConfigureNewrelicForRequestTest extends TestCase
{
    public function setUp(): void
    {
        if (!extension_loaded('newrelic')) {
            require_once __DIR__ . '/../assets/NewrelicFunctions.php';
        }
    }

    public function testItDoesNothingIfNewrelicIsntInstalled(): void
    {
        $request = new ServerRequest();
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        Phony::stubGlobal('extension_loaded', __NAMESPACE__)->returns(false);
        $setAppname = Phony::stubGlobal('newrelic_set_appname', __NAMESPACE__);
        $nameTransaction = Phony::stubGlobal('newrelic_name_Transaction', __NAMESPACE__);
        $addRequestId = Phony::stubGlobal('newrelic_add_custom_parameter', __NAMESPACE__);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->__invoke($request, $response, $next);

        $setAppname->never()->called();
        $nameTransaction->never()->called();
        $addRequestId->never()->called();
    }

    public function testItSetsTheAppName(): void
    {
        $appName = 'testApp';
        $routeName = 'testRoute';
        $requestId = '1000';

        $routeResult = Phony::mock(RouteResult::class);
        $routeResult->isFailure->returns(false);
        $routeResult->getMatchedRouteName->returns($routeName);

        $request = (new ServerRequest())
            ->withAttribute(RouteResult::class, $routeResult->get())
            ->withAttribute('requestId', $requestId);
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        Phony::stubGlobal('extension_loaded', __NAMESPACE__)->returns(true);
        $setAppname = Phony::stubGlobal('newrelic_set_appname', __NAMESPACE__);

        $middleware = new ConfigureNewrelicForRequest($appName);
        $middleware->__invoke($request, $response, $next);

        $setAppname->calledWith($appName);
    }

    public function testItAddsARequestIdParameter(): void
    {
        $requestId = '1000';

        $request = (new ServerRequest())
            ->withAttribute('requestId', $requestId);
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        Phony::stubGlobal('extension_loaded', __NAMESPACE__)->returns(true);
        $addRequestId = Phony::stubGlobal('newrelic_add_custom_parameter', __NAMESPACE__);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->__invoke($request, $response, $next);

        $addRequestId->calledWith('requestId', $requestId);
    }

    public function testItDoesntAddARequestIdWhenOneDoesntExist(): void
    {
        $request = new ServerRequest();
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        Phony::stubGlobal('extension_loaded', __NAMESPACE__)->returns(true);
        $addRequestId = Phony::stubGlobal('newrelic_add_custom_parameter', __NAMESPACE__);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->__invoke($request, $response, $next);

        $addRequestId->never()->called();
    }

    public function testItNamesTheTransaction(): void
    {
        $routeName = 'testRoute';

        $routeResult = Phony::mock(RouteResult::class);
        $routeResult->isFailure->returns(false);
        $routeResult->getMatchedRouteName->returns($routeName);

        $request = (new ServerRequest())
            ->withAttribute(RouteResult::class, $routeResult->get());
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        Phony::stubGlobal('extension_loaded', __NAMESPACE__)->returns(true);
        $nameTransaction = Phony::stubGlobal('newrelic_name_transaction', __NAMESPACE__);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->__invoke($request, $response, $next);

        $nameTransaction->calledWith($routeName);
    }
}
