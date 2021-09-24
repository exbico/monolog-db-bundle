MONOLOG DB BUNDLE
=================

[![Latest Stable Version](https://poser.pugx.org/exbico/monolog-db-bundle/v/stable)](https://packagist.org/packages/exbico/monolog-db-bundle) [![Total Downloads](https://poser.pugx.org/exbico/monolog-db-bundle/downloads)](https://packagist.org/packages/exbico/monolog-db-bundle) [![License](https://poser.pugx.org/drtsb/yii2-seo/license)](https://packagist.org/packages/exbico/monolog-db-bundle)

## INSTALLATION
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```bash
composer require exbico/monolog-db-bundle
```
or add

```
"exbico/monolog-db-bundle": "*"
```

to the require section of your application's `composer.json` file.

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
            'connection'   => 'doctrine.dbal.connection',
            'history_size' => 2,
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

Initialize the tables
```bash
bin/console log:init
```

Rotate the tables
```bash
bin/console log:rotate
```