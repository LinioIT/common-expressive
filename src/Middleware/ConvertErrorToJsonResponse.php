<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Common\Expressive\Exception\Base\DomainException;
use Linio\Common\Expressive\Exception\ExceptionTokens;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Zend\Diactoros\Response\JsonResponse;

class ConvertErrorToJsonResponse
{
    const DEFAULT_STATUS_CODE = 500;

    /**
     * @param mixed $error
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     *
     * @return ResponseInterface
     */
    public function __invoke($error, ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        switch ($error) {
            case $error instanceof DomainException:
                return $this->convertDomainException($error);
            case $error instanceof Throwable:
                return $this->convertThrowable($error);
            default:
                return $this->convertGenericError($error);
        }
    }

    private function convertGenericError($error): JsonResponse
    {
        $body = [
            'code' => ExceptionTokens::AN_ERROR_HAS_OCCURRED,
            'message' => 'A unexpected error has occurred. Please check the logs for more information.',
            'errors' => [],
        ];

        return new JsonResponse($body, self::DEFAULT_STATUS_CODE);
    }

    private function convertThrowable(Throwable $throwable): JsonResponse
    {
        return $this->convertGenericError($throwable->getMessage());
    }

    private function convertDomainException(DomainException $domainException): JsonResponse
    {
        $body = [
            'code' => $domainException->getToken(),
            'message' => $domainException->getMessage(),
            'errors' => $domainException->getErrors(),
        ];

        return new JsonResponse($body, $domainException->getCode());
    }
}
