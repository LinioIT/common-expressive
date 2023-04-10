<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Exception\Http\RouteNotFoundException;
use Linio\Common\Laminas\Middleware\ValidateRequestBody;
use Linio\Common\Laminas\Validation\ValidationService;
use Linio\TestAssets\TestValidationRules;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Router\RouteResult;

class ValidateRequestBodyTest extends TestCase
{
    use ProphecyTrait;

    public function testItSkipsValidationIfTheRouterHasntRun()
    {
        $validationService = $this->prophesize(ValidationService::class);

        $request = new ServerRequest();
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new ValidateRequestBody($validationService->reveal(), []);
        $middleware->__invoke($request, $response, $next);
    }

    public function testItSkipsValidationIfARouteIsNotFound()
    {
        $validationService = $this->prophesize(ValidationService::class);

        $routeResult = RouteResult::fromRouteFailure();
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult);

        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new ValidateRequestBody($validationService->reveal(), []);
        $middleware->__invoke($request, $response, $next);
    }

    public function testItFailsValidationIfTheRouteIsNotFoundInRoutes()
    {
        $validationService = $this->prophesize(ValidationService::class);

        $routeResult = RouteResult::fromRouteMatch('invalid', 'TestMiddleware', []);
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult);

        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $this->expectException(RouteNotFoundException::class);

        $middleware = new ValidateRequestBody($validationService->reveal(), []);
        $middleware->__invoke($request, $response, $next);
    }

    public function testItCallsTheValidatorService()
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $validationService = $this->prophesize(ValidationService::class);
        $validationService
            ->validate([], [TestValidationRules::class])
            ->shouldBeCalled();

        $routeResult = RouteResult::fromRouteMatch('test', 'TestMiddleware', []);
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult)->withParsedBody([]);

        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $middleware = new ValidateRequestBody($validationService->reveal(), $routes);
        $middleware->__invoke($request, $response, $next);
    }
}
