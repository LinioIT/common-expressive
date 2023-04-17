<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Tests\Middleware;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Exception\Http\RouteNotFoundException;
use Linio\Common\Laminas\Middleware\ValidateRequestBody;
use Linio\Common\Laminas\Validation\ValidationService;
use Linio\TestAssets\TestMiddleware;
use Linio\TestAssets\TestValidationRules;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateRequestBodyTest extends TestCase
{
    use ProphecyTrait;

    public function testItSkipsValidationIfTheRouterHasNotRun(): void
    {
        $validationService = $this->prophesize(ValidationService::class);

        $request = new ServerRequest();
        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new ValidateRequestBody($validationService->reveal(), []);
        $middleware->process($request, $handler->reveal());
    }

    public function testItSkipsValidationIfARouteIsNotFound(): void
    {
        $validationService = $this->prophesize(ValidationService::class);

        $routeResult = RouteResult::fromRouteFailure(['GET']);
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult);

        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $this->expectException(MiddlewareOutOfOrderException::class);

        $middleware = new ValidateRequestBody($validationService->reveal(), []);
        $middleware->process($request, $handler->reveal());
    }

    public function testItFailsValidationIfTheRouteIsNotFoundInRoutes(): void
    {
        $validationService = $this->prophesize(ValidationService::class);

        $route = new Route('invalid', new TestMiddleware(), ['GET'], 'invalid');

        $routeResult = RouteResult::fromRoute($route);
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult);

        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $this->expectException(RouteNotFoundException::class);

        $middleware = new ValidateRequestBody($validationService->reveal(), []);
        $middleware->process($request, $handler->reveal());
    }

    public function testItCallsTheValidatorService(): void
    {
        $routes = require __DIR__ . '/../assets/routes.php';

        $validationService = $this->prophesize(ValidationService::class);
        $validationService
            ->validate([], [TestValidationRules::class])
            ->shouldBeCalled();

        $route = new Route('test', new TestMiddleware(), ['GET'], 'test');
        $routeResult = RouteResult::fromRoute($route);
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult)->withParsedBody([]);

        $response = new Response();
        $handler = $this->prophesize(RequestHandlerInterface::class);
        $handler->handle($request)->willReturn($response);

        $middleware = new ValidateRequestBody($validationService->reveal(), $routes);
        $middleware->process($request, $handler->reveal());
    }
}
