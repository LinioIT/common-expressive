<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Support;

use Linio\Common\Laminas\Exception\Http\RouteNotFoundException;
use Mezzio\Router\Route;
use Mezzio\Router\RouteCollector;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @throws RouteNotFoundException
 */
function getCurrentRouteFromMatchedRoute(RouteResult $routeResult, RouteCollector $routes): Route
{
    $routeName = $routeResult->getMatchedRouteName();

    foreach ($routes->getRoutes() as $route) {
        if ($route->getName() == $routeName) {
            return $route;
        }
    }

    throw new RouteNotFoundException();
}

/**
 * @throws RouteNotFoundException
 */
function getCurrentRouteFromRawRoutes(ServerRequestInterface $request, RouteCollector $routes): Route
{
    $routePath = $request->getUri()->getPath();

    foreach ($routes->getRoutes() as $route) {
        if ($route->getPath() == $routePath) {
            return $route;
        }
    }

    throw new RouteNotFoundException();
}
