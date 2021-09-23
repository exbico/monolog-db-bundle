<?php

declare(strict_types=1);

use Doctrine\DBAL\Connection;
use Exbico\MonologDbBundle\Command\InitCommand;
use Exbico\MonologDbBundle\Command\RotateCommand;
use Exbico\MonologDbBundle\MonologDbHandler;
use Exbico\MonologDbBundle\Service\Initializer;
use Exbico\MonologDbBundle\Service\InitializerInterface;
use Exbico\MonologDbBundle\Service\Rotator;
use Exbico\MonologDbBundle\Service\RotatorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(Initializer::class, Initializer::class)
        ->args([service(Connection::class)]);

    $services->alias(InitializerInterface::class, Initializer::class);

    $services->set(Rotator::class, Rotator::class)
        ->args([service(Connection::class), 2]);
    $services->alias(RotatorInterface::class, Rotator::class);

    $services->set(InitCommand::class)
        ->tag('console.command')
        ->args([service(InitializerInterface::class)]);

    $services->set(RotateCommand::class)
        ->tag('console.command')
        ->args([service(RotatorInterface::class)]);

    $services->set(MonologDbHandler::class, MonologDbHandler::class)
        ->args([service(Connection::class)]);
    $services->alias('exbico.monolog_db_handler', MonologDbHandler::class);
};
