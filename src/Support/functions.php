<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Support;

use Linio\Common\Expressive\Exception\Http\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;

/**
 * @param RouteResult $routeResult
 * @param array $routes
 *
 * @throws RouteNotFoundException
 *
 * @return array
 */
function getCurrentRouteFromMatchedRoute(RouteResult $routeResult, array $routes): array
{
    $routeName = $routeResult->getMatchedRouteName();

    foreach ($routes as $route) {
        if (isset($route['name']) && $route['name'] == $routeName) {
            return $route;
        }
    }

    throw new RouteNotFoundException();
}

/**
 * @param ServerRequestInterface $request
 * @param array $routes
 *
 * @throws RouteNotFoundException
 *
 * @return array
 */
function getCurrentRouteFromRawRoutes(ServerRequestInterface $request, array $routes): array
{
    $routePath = $request->getUri()->getPath();

    foreach ($routes as $route) {
        if (isset($route['path']) && $route['path'] == $routePath) {
            return $route;
        }
    }

    throw new RouteNotFoundException();
}
