<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Logging;

use InvalidArgumentException;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LogFactoryTest extends TestCase
{
    public function testItMakesALogger()
    {
        $container = $this->prophesize(ContainerInterface::class);

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
        $container->get('config')->willReturn($config);
        $container->get(NullHandler::class)->willReturn($handler);

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
        $container->get('config')->willReturn($config);
        $container->get(InvalidArgumentException::class)->willThrow(new InvalidArgumentException());

        $this->expectException(InvalidArgumentException::class);

        $factory = new LogFactory($container->reveal());
        $factory->makeLogger('default');
    }
}
