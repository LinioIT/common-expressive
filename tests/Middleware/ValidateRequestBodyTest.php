<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Linio\Common\Expressive\Validation\ValidationService;
use Linio\TestAssets\TestValidationRules;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouteResult;

class ValidateRequestBodyTest extends TestCase
{
    public function testItSkipsValidationIfARouteIsNotFound()
    {
        $validationService = $this->prophesize(ValidationService::class);

        $request = new ServerRequest();
        $response = new EmptyResponse();

        $delegate = $this->prophesize(DelegateInterface::class);
        $delegate->process($request)->willReturn($response);

        $middleware = new ValidateRequestBody($validationService->reveal());
        $actual = $middleware->process($request, $delegate->reveal());

        $this->assertSame($response, $actual);
    }

    public function testItCallsTheValidatorService()
    {
        $validationService = $this->prophesize(ValidationService::class);
        $validationService->validate([], [TestValidationRules::class])->shouldBeCalled();

        $route = new Route('/test', 'Middleware', Route::HTTP_METHOD_ANY);
        $route->setOptions(['validation_rules' => [TestValidationRules::class]]);

        $routeResult = RouteResult::fromRoute($route);
        $request = (new ServerRequest())->withAttribute(RouteResult::class, $routeResult)->withParsedBody([]);

        $delegate = new class() implements DelegateInterface {
            public function process(ServerRequestInterface $request)
            {
                return new EmptyResponse();
            }
        };

        $middleware = new ValidateRequestBody($validationService->reveal());
        $middleware->process($request, $delegate);
    }
}
