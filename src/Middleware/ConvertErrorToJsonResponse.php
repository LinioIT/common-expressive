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
        return new JsonResponse(self::buildErrorBody($error), self::getStatusCode($error));
    }

    public static function buildErrorBody($error): array
    {
        switch ($error) {
            case $error instanceof DomainException:
                return self::buildDomainExceptionBody($error);
            case $error instanceof Throwable:
                return self::buildThrowableBody($error);
            default:
                return self::buildGenericErrorBody($error);
        }
    }

    public static function getStatusCode($error): int
    {
        switch ($error) {
            case $error instanceof DomainException:
                return (int) $error->getCode();
            default:
                return self::DEFAULT_STATUS_CODE;
        }
    }

    private static function buildGenericErrorBody($error): array
    {
        return self::buildBody();
    }

    private static function buildThrowableBody(Throwable $throwable): array
    {
        return self::buildGenericErrorBody($throwable->getMessage());
    }

    private static function buildDomainExceptionBody(DomainException $domainException): array
    {
        return self::buildBody(
            $domainException->getToken(),
            $domainException->getMessage(),
            $domainException->getErrors()
        );
    }

    private static function buildBody(
        string $code = ExceptionTokens::AN_ERROR_HAS_OCCURRED,
        string $message = 'A unexpected error has occurred. Please check the logs for more information.',
        array $errors = []
    ): array {
        return [
            'code' => $code,
            'message' => $message,
            'errors' => $errors,
        ];
    }
}
