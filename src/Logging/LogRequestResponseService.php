<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Logging;

use Linio\Common\Expressive\Exception\Http\MiddlewareOutOfOrderException;
use Linio\Common\Expressive\Filter\FilterService;
use function Linio\Common\Expressive\Support\getCurrentRouteFromRawRoutes;
use Linio\Component\Util\Json;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class LogRequestResponseService
{
    private FilterService $filterService;
    private LoggerInterface $logger;
    private array $routes;

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
        array $routes,
        callable $getRequestLogBody,
        callable $getResponseLogBody
    ) {
        $this->filterService = $filterService;
        $this->logger = $logger;
        $this->routes = $routes;
        $this->getRequestLogBody = $getRequestLogBody;
        $this->getResponseLogBody = $getResponseLogBody;
    }

    public function logRequest(ServerRequestInterface $request)
    {
        $requestData = $this->mapRequestToLogContext($request);

        $this->logger->info('A request has been created.', $requestData);
    }

    public function logResponse(ServerRequestInterface $request, ResponseInterface $response)
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
        } catch (LogicException $exception) {
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
        } catch (LogicException $exception) {
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

        if (empty($matchedRoute['filter_rules'])) {
            return [];
        }

        $rules = $matchedRoute['filter_rules'];

        if (!is_array($rules)) {
            return [$rules];
        }

        return $rules;
    }
}
