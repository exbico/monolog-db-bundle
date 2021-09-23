MONOLOG DB BUNDLE
=================

## INSTALLATION

```bash
composer require exbico/monolog-db-bundle
```

Your bundle should be automatically enabled by Flex. In case you don't use Flex, you'll need to manually enable the bundle by adding the following line in the `config/bundles.php` file of your project:

```php
Exbico\MonologDbBundle\ExbicoMonologDbBundle::class => ['all' => true],
```

## CONFIGURATION

Example of `config/packages/exbico_monolog_db.php` with default values
```php
<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(
        'exbico_monolog_db',
        [
            'connection'   => 'doctrine.dbal.log_connection',
            'history_size' => 3,
        ],
    );
};
```

Example of `config/packages/monolog.php`
```php
<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(
        'monolog',
        [
            'handlers' => [
                'db'                => [
                    'type'     => 'service',
                    'id'       => 'exbico.monolog_db_handler',
                    'level'    => 'debug',
                ],
            ],
        ],
    );
};
```

## USAGE

```bash
bin/console log:init
```

```bash
bin/console log:rotate
```