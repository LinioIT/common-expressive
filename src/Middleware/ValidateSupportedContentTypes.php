<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Common\Expressive\Exception\Http\ContentTypeNotSupportedException;
use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Expressive\Exception\Http\RouteNotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Router\RouteResult;
use function Linio\Common\Expressive\Support\getCurrentRouteFromMatchedRoute;

class ValidateSupportedContentTypes
{
    const DEFAULT_CONTENT_TYPES = ['application/json'];

    /**
     * @var array
     */
    private $supportedContentTypes = [];

    /**
     * @var array
     */
    private $routes;

    /**
     * @param array $supportedContentTypes
     * @param array $routes
     */
    public function __construct(array $supportedContentTypes, array $routes = [])
    {
        $this->supportedContentTypes = $supportedContentTypes;
        $this->routes = $routes;
    }

    /**
     * @param string|null $contentType Null allows non-api requests
     *
     * @return self
     */
    public function supportType(string $contentType = null): self
    {
        $this->supportedContentTypes[] = $contentType;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $contentType = $request->getHeader('Content-Type')[0] ?? null;

        try {
            $this->matchContentTypeFromRoute($contentType, $request);

            return $next($request, $response);
        } catch (RouteNotFoundException $exception) {
            // Fallback to non-route specific types
        }

        if (in_array($contentType, $this->supportedContentTypes)) {
            return $next($request, $response);
        }

        throw new ContentTypeNotSupportedException($contentType);
    }

    /**
     * @param string|null $contentType
     * @param ServerRequestInterface $request
     */
    private function matchContentTypeFromRoute($contentType, ServerRequestInterface $request)
    {
        $routeResult = $request->getAttribute(RouteResult::class);

        if (!$routeResult instanceof RouteResult || !$routeResult->isSuccess()) {
            throw new MiddlewareOutOfOrderException(ApplicationFactory::ROUTING_MIDDLEWARE, self::class);
        }

        $routeConfig = getCurrentRouteFromMatchedRoute($routeResult, $this->routes);

        if (isset($routeConfig['content_types']) && is_array($routeConfig['content_types'])) {
            if (!in_array($contentType, $routeConfig['content_types'])) {
                throw new ContentTypeNotSupportedException($contentType);
            }
        }
    }
}
