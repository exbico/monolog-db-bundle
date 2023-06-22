<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\DependencyInjection;

use Exbico\Handler\Connection\DoctrineDbalAdapter;
use Exbico\Handler\DbHandler;
use Exbico\Handler\DbHandlerConfig;
use Exbico\MonologDbBundle\Command\InitCommand;
use Exbico\MonologDbBundle\Command\RotateCommand;
use Exbico\MonologDbBundle\Service\Initialization\Initializer;
use Exbico\MonologDbBundle\Service\Rotation\RotationConfig;
use Exbico\MonologDbBundle\Service\Rotation\Rotator;
use Exbico\MonologDbBundle\Service\TableCreation\TableCreator;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\BadMethodCallException;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * @phpstan-type Levels array{
 *     emergency: string|null,
 *     alert: string|null,
 *     critical: string|null,
 *     error: string|null,
 *     warning: string|null,
 *     notice: string|null,
 *     info: string|null,
 *     debug: string|null,
 * }
 * @phpstan-type Rotation array{history_size: int, date_format: string}
 */
final class ConfigurationPass implements CompilerPassInterface
{
    /**
     * @throws OutOfBoundsException
     * @throws ServiceNotFoundException
     * @throws BadMethodCallException
     */
    public function process(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('exbico_monolog_db');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $connection = $container->getDefinition($config['connection']);

        $logConnection = new Definition(DoctrineDbalAdapter::class, [$connection]);
        $container->setDefinition('exbico_monolog_db.doctrine_adapter', $logConnection);

        $container->getDefinition(DbHandler::class)
            ->replaceArgument(0, $logConnection)
            ->replaceArgument(1, $this->getHandlerConfigDefinition($config['levels']));

        $handlerConfig = $this->getHandlerConfig($config['levels']);

        $container->getDefinition(TableCreator::class)->replaceArgument(0, $connection);

        $container->getDefinition(Initializer::class)->replaceArgument(0, $connection);

        $container->getDefinition(InitCommand::class)->replaceArgument(1, $handlerConfig->getTables());

        $container->getDefinition(Rotator::class)->replaceArgument(0, $connection);

        $container->setDefinition(
            'exbico_monolog_db.rotation_config',
            $this->getRotationConfigDefinition($config['rotation']),
        );

        $container->getDefinition(RotateCommand::class)->replaceArgument(2, $handlerConfig->getTables());
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param array<array<string,mixed>> $configs
     * @return array{connection: string, levels: Levels, rotation: Rotation}
     */
    private function processConfiguration(ConfigurationInterface $configuration, array $configs): array
    {
        /** @phpstan-ignore-next-line */
        return (new Processor())->processConfiguration($configuration, $configs);
    }

    /**
     * @param array $levels
     * @phpstan-param Levels $levels
     * @return Definition
     */
    private function getHandlerConfigDefinition(array $levels): Definition
    {
        return new Definition(
            DbHandlerConfig::class,
            [
                $levels['emergency'],
                $levels['alert'],
                $levels['critical'],
                $levels['error'],
                $levels['warning'],
                $levels['notice'],
                $levels['info'],
                $levels['debug'],
            ],
        );
    }

    /**
     * @param array $levels
     * @phpstan-param Levels $levels
     * @return DbHandlerConfig
     */
    private function getHandlerConfig(array $levels): DbHandlerConfig
    {
        return new DbHandlerConfig(
            emergency: $levels['emergency'],
            alert    : $levels['alert'],
            critical : $levels['critical'],
            error    : $levels['error'],
            warning  : $levels['warning'],
            notice   : $levels['notice'],
            info     : $levels['info'],
            debug    : $levels['debug'],
        );
    }

    /**
     * @param array $rotation
     * @phpstan-param Rotation $rotation
     * @return Definition
     */
    private function getRotationConfigDefinition(array $rotation): Definition
    {
        return new Definition(
            RotationConfig::class,
            [
                $rotation['history_size'],
                $rotation['date_format'],
            ],
        );
    }
}
