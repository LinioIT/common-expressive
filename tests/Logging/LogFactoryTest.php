<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Tests\Logging;

use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Linio\Common\Expressive\Logging\LogFactory;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class LogFactoryTest extends TestCase
{
    use ProphecyTrait;

    public function testItMakesALogger()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->get('config')
            ->willReturn(
                [
                    'logging' => [
                        'channels' => [
                            'default' => [
                                'handlers' => [
                                    NullHandler::class,
                                ],
                            ],
                        ],
                    ],
                ]
            );
        $container
            ->get(NullHandler::class)
            ->willReturn(new NullHandler());

        $factory = new LogFactory($container->reveal());

        $logger = $factory->makeLogger('default');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
    }

    public function testItMakesALoggerViaConfiguration()
    {
        $config = [
            'logging' => [
                'channels' => [
                    'default' => [
                        'handlers' => [
                            NullHandler::class,
                        ],
                    ],
                ],
            ],
        ];

        $handler = new NullHandler();

        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->get('config')
            ->willReturn($config);
        $container
            ->get(NullHandler::class)
            ->willReturn($handler);

        $factory = new LogFactory($container->reveal());

        /** @var Logger $logger */
        $logger = $factory->makeLogger('default');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertSame($logger->getHandlers()[0], $handler);
    }

    public function testItFailsIfAnInvalidHandlerIsGiven()
    {
        $config = [
            'logging' => [
                'channels' => [
                    'default' => [
                        'handlers' => [
                            InvalidArgumentException::class,
                        ],
                    ],
                ],
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);
        $container
            ->get('config')
            ->willReturn($config);
        $container
            ->get(InvalidArgumentException::class)
            ->willThrow(new InvalidArgumentException());

        $this->expectException(InvalidArgumentException::class);

        $factory = new LogFactory($container->reveal());
        $factory->makeLogger('default');
    }
}
