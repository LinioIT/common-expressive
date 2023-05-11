<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Middleware;

use Linio\Common\Laminas\Exception\Http\ContentTypeNotSupportedException;
use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Exception\Http\RouteNotFoundException;
use function Linio\Common\Laminas\Support\getCurrentRouteFromMatchedRoute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Container\ApplicationFactory;
use Mezzio\Router\RouteResult;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidateSupportedContentTypes implements MiddlewareInterface
{
    public const DEFAULT_CONTENT_TYPES = ['application/json'];

    private array $supportedContentTypes = [];
    private array $routes;

    public function __construct(array $supportedContentTypes, array $routes = [])
    {
        $this->supportedContentTypes = $supportedContentTypes;
        $this->routes = $routes;
    }

    /**
     * @param ?string $contentType Null allows non-api requests
     */
    public function supportType(?string $contentType = null): self
    {
        $this->supportedContentTypes[] = $contentType;

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeader('Content-Type')[0] ?? null;

        try {
            $this->matchContentTypeFromRoute($contentType, $request);

            return $handler->handle($request);
        } catch (RouteNotFoundException $exception) {
            // Fallback to non-route specific types
        }

        if (in_array($contentType, $this->supportedContentTypes)) {
            return $handler->handle($request);
        }

        throw new ContentTypeNotSupportedException($contentType);
    }

    private function matchContentTypeFromRoute(?string $contentType, ServerRequestInterface $request)
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
