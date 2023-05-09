<?php

declare(strict_types=1);

namespace Linio\Common\Laminas\Logging;

use Linio\Component\Microlog\Log;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LogFactory
{
    private ContainerInterface $container;
    private array $loggingConfig;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->loggingConfig = (array) $container->get('config')['logging'];
    }

    /**
     * Adds the loggers and parsers to the static log service.
     */
    public function configureStaticLogService(): void
    {
        foreach ($this->loggingConfig['channels'] as $channel => $config) {
            Log::setLoggerForChannel($this->makeLogger($channel), $channel);
        }

        foreach ($this->loggingConfig['parsers'] as $parserClass) {
            Log::addParser(new $parserClass());
        }
    }

    public function makeLogger(string $channel): LoggerInterface
    {
        $logger = new Logger($channel);

        if (!isset($this->loggingConfig['channels'][$channel]['handlers'])) {
            return $logger;
        }

        foreach ($this->loggingConfig['channels'][$channel]['handlers'] as $handlerService) {
            $handler = $this->container->get($handlerService);

            if (!$handler instanceof HandlerInterface) {
                throw new \InvalidArgumentException(sprintf('Handler [%s] must implement %s', get_class($handler), HandlerInterface::class));
            }

            $logger->pushHandler($handler);
        }

        return $logger;
    }
}
