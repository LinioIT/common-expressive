<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Middleware;

use Linio\Common\Expressive\Exception\Http\ContentTypeNotSupportedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ValidateSupportedContentTypes
{
    /**
     * @var array
     */
    private $supportedContentTypes = ['application/json'];

    /**
     * @param array $supportedContentTypes
     */
    public function __construct(array $supportedContentTypes = [])
    {
        if (!empty($supportedContentTypes)) {
            $this->supportedContentTypes = $supportedContentTypes;
        }
    }

    /**
     * @param string $contentType
     *
     * @return self
     */
    public function supportType(string $contentType): self
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
        if (!$request->hasHeader('Content-Type')) {
            throw new ContentTypeNotSupportedException();
        }

        $contentType = $request->getHeader('Content-Type')[0];

        if (!in_array($contentType, $this->supportedContentTypes)) {
            throw new ContentTypeNotSupportedException();
        }

        return $next($request, $response);
    }
}
