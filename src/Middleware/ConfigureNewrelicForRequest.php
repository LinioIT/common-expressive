<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;

class ConfigureNewrelicForRequest
{
    /**
     * @var string
     */
    private $appName;

    public function __construct(string $appName)
    {
        $this->appName = $appName;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if (!extension_loaded('newrelic')) {
            return $next($request, $response);
        }

        newrelic_set_appname($this->appName);
        $this->addRequestIdToNewrelic($request);
        $this->nameRouteIfRouteFound($request);

        return $next($request, $response);
    }

    private function nameRouteIfRouteFound(ServerRequestInterface $request)
    {
        /** @var RouteResult $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult instanceof RouteResult || $routeResult->isFailure()) {
            return;
        }

        $routeName = $routeResult->getMatchedRouteName();

        newrelic_name_transaction($routeName);
    }

    /**
     * @throws MiddlewareOutOfOrderException
     */
    private function addRequestIdToNewrelic(ServerRequestInterface $request)
    {
        $requestId = $request->getAttribute('requestId', false);

        if (!$requestId) {
            return;
        }

        newrelic_add_custom_parameter('requestId', $requestId);
    }
}
