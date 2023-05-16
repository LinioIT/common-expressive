<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Exception\Http\RouteNotFoundException;
use Linio\Component\Util\Json;
use Mezzio\Router\RouteCollector;
use function Linio\Common\Laminas\Support\getCurrentRouteFromMatchedRoute;
use Linio\Common\Laminas\Validation\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateRequestBody implements MiddlewareInterface
{
    private ValidationService $validationService;
    private RouteCollector $routeCollector;


    public function __construct(ValidationService $validationService, RouteCollector $routeCollector)
    {
        $this->validationService = $validationService;
        $this->routeCollector = $routeCollector;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult instanceof RouteResult || $routeResult->isFailure()) {
            throw new MiddlewareOutOfOrderException('Routing Middleware', self::class);
        }

        $validationClasses = $this->getValidationRuleClasses($routeResult);

        $streamBody = $request->getBody();
        $requestBody = (string) $streamBody->getContents();
        $parsedBody = Json::decode($requestBody);

        if (!empty($validationClasses)) {
            $this->validationService->validate($parsedBody, $validationClasses);
        }

        $streamBody->rewind();

        return $handler->handle($request);
    }

    /**
     * @throws RouteNotFoundException
     */
    private function getValidationRuleClasses(RouteResult $routeResult): array
    {
        $matchedRoute = getCurrentRouteFromMatchedRoute($routeResult, $this->routeCollector);
        $matchedRouteOptions = $matchedRoute->getOptions();

        $matchedRouteOptions = [];

        if (empty($matchedRouteOptions['validation_rules'])) {
            return [];
        }

        $rules = $matchedRouteOptions['validation_rules'];

        if (!is_array($rules)) {
            return [$rules];
        }

        return $rules;
    }
}
