<?php

declare(strict_types=1);

namespace Linio\Common\Expressive\Logging;

use Eloquent\Phony\Phony;
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
        $container = Phony::mock(ContainerInterface::class);

        $factory = new LogFactory($container->get());

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

        $container = Phony::mock(ContainerInterface::class);
        $container->get->with('config')->returns($config);
        $container->get->with(NullHandler::class)->returns($handler);

        $factory = new LogFactory($container->get());

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

        $container = Phony::mock(ContainerInterface::class);
        $container->get->with('config')->returns($config);
        $container->get->with(InvalidArgumentException::class)->returns(new InvalidArgumentException());

        $this->expectException(InvalidArgumentException::class);

        $factory = new LogFactory($container->get());
        $factory->makeLogger('default');
    }
}
