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
            \Zend\Expressive\Helper\ServerUrlHelper::class => \Zend\Expressive\Helper\ServerUrlHelper::class,
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            \Zend\Expressive\Application::class => \Zend\Expressive\Container\ApplicationFactory::class,
            \Zend\Expressive\Helper\UrlHelper::class => \Zend\Expressive\Helper\UrlHelperFactory::class,
            \Particle\Validator\Validator::class => function (ContainerInterface $container) {
                return new \Particle\Validator\Validator();
            },
            \Linio\Common\Expressive\Validation\ValidatorFactory::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Validation\ValidatorFactory(
                    $container,
                    \Particle\Validator\Validator::class
                );
            },
            \Linio\Common\Expressive\Validation\ValidationRulesFactory::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Validation\ValidationRulesFactory($container);
            },
            \Linio\Common\Expressive\Validation\ValidationService::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Validation\ValidationService(
                    $container->get(\Linio\Common\Expressive\Validation\ValidatorFactory::class),
                    $container->get(\Linio\Common\Expressive\Validation\ValidationRulesFactory::class)
                );
            },
            \Linio\Common\Expressive\Filter\FilterRulesFactory::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Filter\FilterRulesFactory($container);
            },
            \Linio\Common\Expressive\Filter\FilterService::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Filter\FilterService(
                    \Particle\Filter\Filter::class,
                    $container->get(\Linio\Common\Expressive\Filter\FilterRulesFactory::class)
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
            \Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class => \Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class,
            \Linio\Common\Expressive\Middleware\ConvertErrorToJsonResponse::class => \Linio\Common\Expressive\Middleware\ConvertErrorToJsonResponse::class,
            \Linio\Common\Expressive\Middleware\AddRequestIdToRequest::class => \Linio\Common\Expressive\Middleware\AddRequestIdToRequest::class,
            \Linio\Common\Expressive\Middleware\AddRequestIdToResponse::class => \Linio\Common\Expressive\Middleware\AddRequestIdToResponse::class,
            \Linio\Common\Expressive\Middleware\LogExceptions::class => \Linio\Common\Expressive\Middleware\LogExceptions::class,
        ],
        'factories' => [
            \Zend\Expressive\Helper\ServerUrlMiddleware::class => \Zend\Expressive\Helper\ServerUrlMiddlewareFactory::class,
            \Zend\Expressive\Helper\UrlHelperMiddleware::class => \Zend\Expressive\Helper\UrlHelperMiddlewareFactory::class,
            \Linio\Common\Expressive\Middleware\ValidateRequestBody::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Middleware\ValidateRequestBody(
                    $container->get(\Linio\Common\Expressive\Validation\ValidationService::class), $container->get('config')['routes']
                );
            },
            \Linio\Common\Expressive\Middleware\LogRequest::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Middleware\LogRequest(
                    $container->get(\Linio\Common\Expressive\Logging\LogRequestResponseService::class)
                );
            },
            \Linio\Common\Expressive\Middleware\LogResponse::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Middleware\LogResponse(
                    $container->get(\Linio\Common\Expressive\Logging\LogRequestResponseService::class)
                );
            },
            \Linio\Common\Expressive\Middleware\ConfigureNewrelicForRequest::class => function (ContainerInterface $container) {
                $config = $container->get('config');

                return new \Linio\Common\Expressive\Middleware\ConfigureNewrelicForRequest($config['logging']['newRelic']['appName']);
            },
            \Linio\Common\Expressive\Middleware\ValidateSupportedContentTypes::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Middleware\ValidateSupportedContentTypes(\Linio\Common\Expressive\Middleware\ValidateSupportedContentTypes::DEFAULT_CONTENT_TYPES);
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
                \Zend\Expressive\Helper\ServerUrlMiddleware::class,
                \Linio\Common\Expressive\Middleware\AddRequestIdToRequest::class,
                \Linio\Common\Expressive\Middleware\AddRequestIdToLog::class,
                \Linio\Common\Expressive\Middleware\LogResponse::class,
                \Linio\Common\Expressive\Middleware\AddRequestIdToResponse::class,
                \Linio\Common\Expressive\Middleware\LogRequest::class,
            ],
            'priority' => 10000,
        ],
        'routing' => [
            'middleware' => [
                \Zend\Expressive\Container\ApplicationFactory::ROUTING_MIDDLEWARE,
                \Zend\Expressive\Helper\UrlHelperMiddleware::class,
                \Linio\Common\Expressive\Middleware\ValidateSupportedContentTypes::class,
                \Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware::class,
                \Linio\Common\Expressive\Middleware\ConfigureNewrelicForRequest::class,
                \Linio\Common\Expressive\Middleware\ValidateRequestBody::class,
                // Add more middleware here that needs to introspect the routing
                // results; this might include:
                // - route-based authentication
                // - route-based validation
                // - etc.
                \Zend\Expressive\Container\ApplicationFactory::DISPATCH_MIDDLEWARE,
            ],
            'priority' => 1,
        ],
        'error' => [
            'middleware' => [
                \Linio\Common\Expressive\Middleware\LogExceptions::class,
                \Linio\Common\Expressive\Middleware\ConvertErrorToJsonResponse::class,
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
            \Linio\Common\Expressive\Logging\LogFactory::class => function (ContainerInterface $container) {
                return new \Linio\Common\Expressive\Logging\LogFactory($container);
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
            \Linio\Common\Expressive\Logging\LogRequestResponseService::class => function (ContainerInterface $container) {
                /** @var \Linio\Common\Expressive\Logging\LogFactory $loggingFactory */
                $loggingFactory = $container->get(\Linio\Common\Expressive\Logging\LogFactory::class);

                $config = $container->get('config');

                return new \Linio\Common\Expressive\Logging\LogRequestResponseService(
                    $container->get(\Linio\Common\Expressive\Filter\FilterService::class),
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
