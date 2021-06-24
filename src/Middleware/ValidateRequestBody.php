<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Expressive\Exception\Http\RouteNotFoundException;
use function Linio\Common\Expressive\Support\getCurrentRouteFromMatchedRoute;
use Linio\Common\Expressive\Validation\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;

class ValidateRequestBody
{
    private ValidationService $validationService;

    /**
     * An array of zend-expressive routes.
     */
    private array $routes;

    public function __construct(ValidationService $validationService, array $routes)
    {
        $this->validationService = $validationService;
        $this->routes = $routes;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult instanceof RouteResult || $routeResult->isFailure()) {
            throw new MiddlewareOutOfOrderException('Routing Middleware', self::class);
        }

        $validationClasses = $this->getValidationRuleClasses($routeResult);

        if (!empty($validationClasses)) {
            $this->validationService->validate($request->getParsedBody(), $validationClasses);
        }

        return $next($request, $response);
    }

    /**
     * @throws RouteNotFoundException
     */
    private function getValidationRuleClasses(RouteResult $routeResult): array
    {
        $matchedRoute = getCurrentRouteFromMatchedRoute($routeResult, $this->routes);

        if (empty($matchedRoute['validation_rules'])) {
            return [];
        }

        $rules = $matchedRoute['validation_rules'];

        if (!is_array($rules)) {
            return [$rules];
        }

        return $rules;
    }
}
