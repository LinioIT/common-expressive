<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Linio\Common\Expressive\Validation\ValidationService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;

class ValidateRequestBody implements MiddlewareInterface
{
    /**
     * @var ValidationService
     */
    private $validationService;

    public function __construct(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult instanceof RouteResult || $routeResult->isFailure()) {
            return $delegate->process($request);
        }

        $validationClasses = $this->getValidationRuleClasses($routeResult);

        if (!empty($validationClasses)) {
            $this->validationService->validate($request->getParsedBody(), $validationClasses);
        }

        return $delegate->process($request);
    }

    private function getValidationRuleClasses(RouteResult $routeResult): array
    {
        $route = $routeResult->getMatchedRoute();

        if (empty($route->getOptions()['validation_rules'])) {
            return [];
        }

        $rules = $route->getOptions()['validation_rules'];

        if (!is_array($rules)) {
            return [$rules];
        }

        return $rules;
    }
}
