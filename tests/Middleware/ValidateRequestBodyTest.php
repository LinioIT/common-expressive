<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Eloquent\Phony\Phony;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Expressive\Exception\Http\RouteNotFoundException;
use Linio\Common\Expressive\Validation\ValidationService;
use Linio\TestAssets\TestValidationRules;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;

class ValidateRequestBodyTest extends TestCase
{
    public function testItSkipsValidationIfTheRouterHasntRun()
    {
        $validationService = Phony::mock(ValidationService::class);

        $request = new ServerRequest();
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new ValidateRequestBody($validationService->get(), []);
        $middleware->__invoke($request, $response, $next);
    }

    public function testItSkipsValidationIfARouteIsNotFound()
    {
        $validationService = Phony::mock(ValidationService::class);

        $routeResult = RouteResult::fromRouteFailure();
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult);

        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new ValidateRequestBody($validationService->get(), []);
        $middleware->__invoke($request, $response, $next);
    }

    public function testItFailsValidationIfTheRouteIsNotFoundInRoutes()
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $validationService = Phony::mock(ValidationService::class);

        $middleware = $this->prophesize(MiddlewareInterface::class);
        $routeResult = RouteResult::fromRoute(new Route('invalid', $middleware->reveal()), []);
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult);

        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(RouteNotFoundException::class);

        $middleware = new ValidateRequestBody($validationService->get(), []);
        $middleware->__invoke($request, $response, $next);
    }

    public function testItCallsTheValidatorService()
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $validationService = Phony::mock(ValidationService::class);

        $middleware = $this->prophesize(MiddlewareInterface::class);
        $routeResult = RouteResult::fromRoute(new Route('test', $middleware->reveal()), []);
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult)->withParsedBody([]);

        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $middleware = new ValidateRequestBody($validationService->get(), $routes);
        $middleware->__invoke($request, $response, $next);

        $validationService->validate->calledWith([], [TestValidationRules::class]);
    }
}
