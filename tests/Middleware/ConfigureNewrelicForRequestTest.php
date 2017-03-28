<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;

class ConfigureNewrelicForRequestTest extends TestCase
{
    use PHPMock;

    public function setUp()
    {
        if (!extension_loaded('newrelic')) {
            require_once __DIR__ . '/../assets/NewrelicFunctions.php';
        }
    }

    public function testItDoesNothingIfNewrelicIsntInstalled()
    {
        $request = new ServerRequest();
        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request)
            {
                return new EmptyResponse();
            }
        };

        $this->getFunctionMock(__NAMESPACE__, 'extension_loaded')->expects($this->once())->willReturn(false);
        $this->getFunctionMock(__NAMESPACE__, 'newrelic_set_appname')->expects($this->never());
        $this->getFunctionMock(__NAMESPACE__, 'newrelic_name_transaction')->expects($this->never());
        $this->getFunctionMock(__NAMESPACE__, 'newrelic_add_custom_parameter')->expects($this->never());

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->process($request, $delegate);
    }

    public function testItSetsTheAppName()
    {
        $appName = 'testApp';
        $routeName = 'testRoute';
        $requestId = '1000';

        $routeResult = RouteResult::fromRoute(new Route('test', 'middleware', Route::HTTP_METHOD_ANY, $routeName));

        $request = (new ServerRequest())
            ->withAttribute(RouteResult::class, $routeResult)
            ->withAttribute('requestId', $requestId);
        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request)
            {
                return new EmptyResponse();
            }
        };

        $this->getFunctionMock(__NAMESPACE__, 'extension_loaded')->expects($this->once())->willReturn(true);
        $this->getFunctionMock(__NAMESPACE__, 'newrelic_set_appname')->expects($this->once())->with($appName);

        $middleware = new ConfigureNewrelicForRequest($appName);
        $middleware->process($request, $delegate);
    }

    public function testItAddsARequestIdParameter()
    {
        $requestId = '1000';

        $request = (new ServerRequest())
            ->withAttribute('requestId', $requestId);
        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request)
            {
                return new EmptyResponse();
            }
        };

        $this->getFunctionMock(__NAMESPACE__, 'extension_loaded')->expects($this->once())->willReturn(true);
        $this->getFunctionMock(__NAMESPACE__, 'newrelic_add_custom_parameter')->expects($this->once())->with('requestId', $requestId);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->process($request, $delegate);
    }

    public function testItDoesntAddARequestIdWhenOneDoesntExist()
    {
        $request = new ServerRequest();
        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request)
            {
                return new EmptyResponse();
            }
        };

        $this->getFunctionMock(__NAMESPACE__, 'extension_loaded')->expects($this->once())->willReturn(true);
        $this->getFunctionMock(__NAMESPACE__, 'newrelic_add_custom_parameter')->expects($this->never());

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->process($request, $delegate);
    }

    public function testItNamesTheTransaction()
    {
        $routeName = 'testRoute';

        $routeResult = RouteResult::fromRoute(new Route('test', 'middleware', Route::HTTP_METHOD_ANY, $routeName));

        $request = (new ServerRequest())
            ->withAttribute(RouteResult::class, $routeResult);
        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request)
            {
                return new EmptyResponse();
            }
        };

        $this->getFunctionMock(__NAMESPACE__, 'extension_loaded')->expects($this->once())->willReturn(true);
        $this->getFunctionMock(__NAMESPACE__, 'newrelic_name_transaction')->expects($this->once())->with($routeName);

        $middleware = new ConfigureNewrelicForRequest('appName');
        $middleware->process($request, $delegate);
    }
}
