<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Middleware;

use Exception;
use Linio\Common\Expressive\Exception\Base\DomainException;
use Linio\Common\Expressive\Exception\ExceptionTokens;
use Linio\Common\Expressive\Middleware\ConvertErrorToJsonResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\EmptyResponse;
use Zend\Diactoros\ServerRequest;

class ConvertErrorToJsonResponseTest extends TestCase
{
    public function testItConvertsAGenericErrorToAResponse()
    {
        $error = 'some error';

        $expected = [
            'code' => ExceptionTokens::AN_ERROR_HAS_OCCURRED,
            'message' => 'A unexpected error has occurred. Please check the logs for more information.',
            'errors' => [],
        ];

        $request = new ServerRequest();
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $middleware = new ConvertErrorToJsonResponse();
        $actual = $middleware->__invoke($error, $request, $response, $next);

        $actualBody = json_decode((string) $actual->getBody(), true);

        $this->assertSame($expected, $actualBody);
    }

    public function testItConvertsThrowablesToAResponse()
    {
        $error = new Exception('Some Message');

        $expected = [
            'code' => ExceptionTokens::AN_ERROR_HAS_OCCURRED,
            'message' => 'A unexpected error has occurred. Please check the logs for more information.',
            'errors' => [],
        ];

        $request = new ServerRequest();
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $middleware = new ConvertErrorToJsonResponse();
        $actual = $middleware->__invoke($error, $request, $response, $next);

        $actualBody = json_decode((string) $actual->getBody(), true);

        $this->assertSame($expected, $actualBody);
    }

    public function testItConvertsDomainExceptionsToAResponse()
    {
        $error = new DomainException('TEST_TOKEN', 599, 'Test Message', [['field' => 'test', 'message' => 'issue']]);

        $expected = [
            'code' => 'TEST_TOKEN',
            'message' => 'Test Message',
            'errors' => [
                [
                    'field' => 'test',
                    'message' => 'issue',
                ],
            ],
        ];

        $request = new ServerRequest();
        $response = new Response();
        $next = function (ServerRequestInterface $request, ResponseInterface $response) {
            return new EmptyResponse();
        };

        $middleware = new ConvertErrorToJsonResponse();
        $actual = $middleware->__invoke($error, $request, $response, $next);

        $actualBody = json_decode((string) $actual->getBody(), true);

        $this->assertSame($expected, $actualBody);
    }
}
