<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Logging;

use Linio\Common\Laminas\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Laminas\Filter\FilterService;

use function Linio\Common\Laminas\Support\getCurrentRouteFromRawRoutes;

use Linio\Component\Util\Json;
use Mezzio\Router\RouteCollector;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LogRequestResponseService
{
    private FilterService $filterService;
    private LoggerInterface $logger;
    private RouteCollector $routes;

    /**
     * @var callable
     */
    private $getRequestLogBody;

    /**
     * @var callable
     */
    private $getResponseLogBody;

    public function __construct(
        FilterService $filterService,
        LoggerInterface $logger,
        RouteCollector $routes,
        callable $getRequestLogBody,
        callable $getResponseLogBody
    ) {
        $this->filterService = $filterService;
        $this->logger = $logger;
        $this->routes = $routes;
        $this->getRequestLogBody = $getRequestLogBody;
        $this->getResponseLogBody = $getResponseLogBody;
    }

    public function logRequest(ServerRequestInterface $request): void
    {
        $requestData = $this->mapRequestToLogContext($request);

        $this->logger->info('A request has been created.', $requestData);
    }

    public function logResponse(ServerRequestInterface $request, ResponseInterface $response): void
    {
        $responseData = $this->mapResponseToLogContext($request, $response);

        $this->logger->info('A response has been created.', $responseData);
    }

    private function mapRequestToLogContext(ServerRequestInterface $request): array
    {
        $filters = $this->getFilterRuleClasses($request);

        try {
            $body = Json::decode((string) $request->getBody());

            if (is_array($body)) {
                $body = $this->filterService->filter($body, $filters);
            }
        } catch (\LogicException $exception) {
            $body = (string) $request->getBody();
        }

        $getRequestLogBody = $this->getRequestLogBody;

        return $getRequestLogBody($request, $body);
    }

    private function mapResponseToLogContext(ServerRequestInterface $request, ResponseInterface $response): array
    {
        $filters = $this->getFilterRuleClasses($request);

        try {
            $body = Json::decode((string) $response->getBody());

            if (is_array($body)) {
                $body = $this->filterService->filter($body, $filters);
            }
        } catch (\LogicException $exception) {
            $body = (string) $response->getBody();
        }

        $getResponseLogBody = $this->getResponseLogBody;

        return $getResponseLogBody($request, $response, $body);
    }

    /**
     * @throws MiddlewareOutOfOrderException
     */
    private function getFilterRuleClasses(ServerRequestInterface $request): array
    {
        $matchedRoute = getCurrentRouteFromRawRoutes($request, $this->routes);

        if (empty($matchedRoute->getOptions()['filter_rules'])) {
            return [];
        }

        $rules = $matchedRoute->getOptions()['filter_rules'];

        if (!is_array($rules)) {
            return [$rules];
        }

        return $rules;
    }
}
