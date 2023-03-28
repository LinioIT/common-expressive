# Linio Common Expressive
This library is used as a basis for all of Linio's zend-expressive applications.

### Simple Setup
The following are examples of configuration for a fresh zend-expressive skeleton

#### dependencies.global.php
```php
<?php

declare(strict_types=1);

use Interop\Container\ContainerInterface;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
            \Mezzio\Helper\ServerUrlHelper::class => \Mezzio\Helper\ServerUrlHelper::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            \Mezzio\Application::class => \Mezzio\Container\ApplicationFactory::class,
            \Mezzio\Helper\UrlHelper::class => \Mezzio\Helper\UrlHelperFactory::class,
            \Particle\Validator\Validator::class => function (ContainerInterface $container) {
                return new \Particle\Validator\Validator();
            },
            \Linio\Common\Mezzio\Validation\ValidatorFactory::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Validation\ValidatorFactory(
                    $container,
                    \Particle\Validator\Validator::class
                );
            },
            \Linio\Common\Mezzio\Validation\ValidationRulesFactory::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Validation\ValidationRulesFactory($container);
            },
            \Linio\Common\Mezzio\Validation\ValidationService::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Validation\ValidationService(
                    $container->get(\Linio\Common\Mezzio\Validation\ValidatorFactory::class),
                    $container->get(\Linio\Common\Mezzio\Validation\ValidationRulesFactory::class)
                );
            },
            \Linio\Common\Mezzio\Filter\FilterRulesFactory::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Filter\FilterRulesFactory($container);
            },
            \Linio\Common\Mezzio\Filter\FilterService::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Filter\FilterService(
                    \Particle\Filter\Filter::class,
                    $container->get(\Linio\Common\Mezzio\Filter\FilterRulesFactory::class)
                );
            },
        ],
        'shared' => [
            \Particle\Validator\Validator::class => false,
        ],
    ],
];
```

#### middleware-pipeline.global.php
```php
<?php

declare(strict_types=1);

use Interop\Container\ContainerInterface;

return [
    'dependencies' => [
        'invokables' => [
            \Mezzio\Helper\BodyParams\BodyParamsMiddleware::class => \Mezzio\Helper\BodyParams\BodyParamsMiddleware::class,
            \Linio\Common\Mezzio\Middleware\ConvertErrorToJsonResponse::class => \Linio\Common\Mezzio\Middleware\ConvertErrorToJsonResponse::class,
            \Linio\Common\Mezzio\Middleware\AddRequestIdToRequest::class => \Linio\Common\Mezzio\Middleware\AddRequestIdToRequest::class,
            \Linio\Common\Mezzio\Middleware\AddRequestIdToResponse::class => \Linio\Common\Mezzio\Middleware\AddRequestIdToResponse::class,
            \Linio\Common\Mezzio\Middleware\LogExceptions::class => \Linio\Common\Mezzio\Middleware\LogExceptions::class,
        ],
        'factories' => [
            \Mezzio\Helper\ServerUrlMiddleware::class => \Mezzio\Helper\ServerUrlMiddlewareFactory::class,
            \Mezzio\Helper\UrlHelperMiddleware::class => \Mezzio\Helper\UrlHelperMiddlewareFactory::class,
            \Linio\Common\Mezzio\Middleware\ValidateRequestBody::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Middleware\ValidateRequestBody(
                    $container->get(\Linio\Common\Mezzio\Validation\ValidationService::class), $container->get('config')['routes']
                );
            },
            \Linio\Common\Mezzio\Middleware\LogRequest::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Middleware\LogRequest(
                    $container->get(\Linio\Common\Mezzio\Logging\LogRequestResponseService::class)
                );
            },
            \Linio\Common\Mezzio\Middleware\LogResponse::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Middleware\LogResponse(
                    $container->get(\Linio\Common\Mezzio\Logging\LogRequestResponseService::class)
                );
            },
            \Linio\Common\Mezzio\Middleware\ConfigureNewrelicForRequest::class => function (ContainerInterface $container) {
                $config = $container->get('config');

                return new \Linio\Common\Mezzio\Middleware\ConfigureNewrelicForRequest($config['logging']['newRelic']['appName']);
            },
            \Linio\Common\Mezzio\Middleware\ValidateSupportedContentTypes::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Middleware\ValidateSupportedContentTypes(\Linio\Common\Mezzio\Middleware\ValidateSupportedContentTypes::DEFAULT_CONTENT_TYPES);
            },
        ],
    ],
    // This can be used to seed pre- and/or post-routing middleware
    'middleware_pipeline' => [
        'always' => [
            'middleware' => [
                // Add more middleware here that you want to execute on
                // every request:
                // - bootstrapping
                // - pre-conditions
                // - modifications to outgoing responses
                \Mezzio\Helper\ServerUrlMiddleware::class,
                \Linio\Common\Mezzio\Middleware\AddRequestIdToRequest::class,
                \Linio\Common\Mezzio\Middleware\AddRequestIdToLog::class,
                \Linio\Common\Mezzio\Middleware\LogResponse::class,
                \Linio\Common\Mezzio\Middleware\AddRequestIdToResponse::class,
                \Linio\Common\Mezzio\Middleware\LogRequest::class,
            ],
            'priority' => 10000,
        ],
        'routing' => [
            'middleware' => [
                \Mezzio\Container\ApplicationFactory::ROUTING_MIDDLEWARE,
                \Mezzio\Helper\UrlHelperMiddleware::class,
                \Linio\Common\Mezzio\Middleware\ValidateSupportedContentTypes::class,
                \Mezzio\Helper\BodyParams\BodyParamsMiddleware::class,
                \Linio\Common\Mezzio\Middleware\ConfigureNewrelicForRequest::class,
                \Linio\Common\Mezzio\Middleware\ValidateRequestBody::class,
                // Add more middleware here that needs to introspect the routing
                // results; this might include:
                // - route-based authentication
                // - route-based validation
                // - etc.
                \Mezzio\Container\ApplicationFactory::DISPATCH_MIDDLEWARE,
            ],
            'priority' => 1,
        ],
        'error' => [
            'middleware' => [
                \Linio\Common\Mezzio\Middleware\LogExceptions::class,
                \Linio\Common\Mezzio\Middleware\ConvertErrorToJsonResponse::class,
            ],
            'error' => true,
            'priority' => -10000,
        ],
    ],
];
```

### logging.global.php
```php
<?php

declare(strict_types=1);

use Interop\Container\ContainerInterface;

return [
    'dependencies' => [
        'factories' => [
            \Linio\Common\Mezzio\Logging\LogFactory::class => function (ContainerInterface $container) {
                return new \Linio\Common\Mezzio\Logging\LogFactory($container);
            },
            'logging.handler.default' => function (ContainerInterface $container) {
                $config = $container->get('config');
                $logPath = $config['logging']['path'];
                $logFile = sprintf('%s/%s.log', $logPath, 'prod');

                $formatter = new \Monolog\Formatter\JsonFormatter();
                $handler = new \Monolog\Handler\StreamHandler($logFile);
                $handler->setFormatter($formatter);

                return $handler;
            },
            'logging.handler.newRelic' => function (ContainerInterface $container) {
                $config = $container->get('config');

                if (!extension_loaded('newrelic')) {
                    return new class() extends \Monolog\Handler\AbstractHandler {
                        public function handle(array $record)
                        {
                            return false;
                        }
                    };
                }

                return new \Monolog\Handler\NewRelicHandler(\Monolog\Logger::CRITICAL, true, $config['logging']['newRelic']['appName']);
            },
            \Linio\Common\Mezzio\Logging\LogRequestResponseService::class => function (ContainerInterface $container) {
                /** @var \Linio\Common\Mezzio\Logging\LogFactory $loggingFactory */
                $loggingFactory = $container->get(\Linio\Common\Mezzio\Logging\LogFactory::class);

                $config = $container->get('config');

                return new \Linio\Common\Mezzio\Logging\LogRequestResponseService(
                    $container->get(\Linio\Common\Mezzio\Filter\FilterService::class),
                    $loggingFactory->makeLogger('request-response'),
                    $config['routes'],
                    $config['logging']['requestResponse']['requestFormatter'],
                    $config['logging']['requestResponse']['responseFormatter']
                );
            },
        ],
    ],
    'logging' => [
        'path' => __DIR__ . '/../../data/logs',
        'newRelic' => [
            'appName' => 'Demo App',
            'enabled' => false,
        ],
        'channels' => [
            'default' => [
                'handlers' => [
                    'logging.handler.default',
                    'logging.handler.newRelic',
                ],
            ],
            'request-response' => [
                'handlers' => [
                    'logging.handler.default',
                ],
            ],
            'exceptions' => [
                'handlers' => [
                    'logging.handler.default',
                    'logging.handler.newRelic',
                ],
            ],
        ],
        'parsers' => [
        ],
    ],
];
```
