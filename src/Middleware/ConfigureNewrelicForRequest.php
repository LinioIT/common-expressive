<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;

class ConfigureNewrelicForRequest implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $appName;

    public function __construct(string $appName)
    {
        $this->appName = $appName;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        if (!extension_loaded('newrelic')) {
            return $delegate->process($request);
        }

        newrelic_set_appname($this->appName);
        $this->addRequestIdToNewrelic($request);
        $this->nameRouteIfRouteFound($request);

        return $delegate->process($request);
    }

    private function nameRouteIfRouteFound(ServerRequestInterface $request): void
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
    private function addRequestIdToNewrelic(ServerRequestInterface $request): void
    {
        $requestId = $request->getAttribute('requestId', false);

        if (!$requestId) {
            return;
        }

        newrelic_add_custom_parameter('requestId', $requestId);
    }
}
