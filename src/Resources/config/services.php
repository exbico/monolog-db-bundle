<?php

declare(strict_types=1);

use Exbico\Handler\DbHandler;
use Exbico\MonologDbBundle\Command\InitCommand;
use Exbico\MonologDbBundle\Command\RotateCommand;
use Exbico\MonologDbBundle\Service\Initialization\Initializer;
use Exbico\MonologDbBundle\Service\Initialization\InitializerInterface;
use Exbico\MonologDbBundle\Service\Rotation\Rotator;
use Exbico\MonologDbBundle\Service\Rotation\RotatorInterface;
use Exbico\MonologDbBundle\Service\TableCreation\TableCreator;
use Exbico\MonologDbBundle\Service\TableCreation\TableCreatorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(TableCreator::class, TableCreator::class)
        ->args([service('doctrine.dbal.connection')]);

    $services->alias(TableCreatorInterface::class, TableCreator::class);

    $services->set(Initializer::class, Initializer::class)
        ->args([service('doctrine.dbal.connection'), service(TableCreatorInterface::class)]);

    $services->alias(InitializerInterface::class, Initializer::class);

    $services->set(Rotator::class, Rotator::class)
        ->args([service('doctrine.dbal.connection'), service(TableCreatorInterface::class)]);
    $services->alias(RotatorInterface::class, Rotator::class);

    $services->set(InitCommand::class)
        ->tag('console.command')
        ->args([service(InitializerInterface::class), []]);

    $services->set(RotateCommand::class)
        ->tag('console.command')
        ->args([service(RotatorInterface::class), service('exbico_monolog_db.rotation_config'), []]);

    $services->set(DbHandler::class, DbHandler::class)
        ->args([service('exbico_monolog_db.log_connection'), null]);
    $services->alias('exbico_monolog_db.handler', DbHandler::class);
};
