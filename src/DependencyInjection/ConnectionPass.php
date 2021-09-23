<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\DependencyInjection;

use Exbico\MonologDbBundle\MonologDbHandler;
use Exbico\MonologDbBundle\Service\Initializer;
use Exbico\MonologDbBundle\Service\Rotator;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class ConnectionPass implements CompilerPassInterface
{
    /**
     * @throws OutOfBoundsException
     * @throws ServiceNotFoundException
     */
    public function process(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('exbico_monolog_db');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if ($container->hasDefinition($config['connection'])) {
            $connection = $container->getDefinition($config['connection']);

            $definition = $container->getDefinition(Initializer::class);
            $definition->replaceArgument(0, $connection);

            $definition = $container->getDefinition(Rotator::class);
            $definition->replaceArgument(0, $connection);

            $definition = $container->getDefinition(MonologDbHandler::class);
            $definition->replaceArgument(0, $connection);
        }
    }

    private function processConfiguration(ConfigurationInterface $configuration, array $configs): array
    {
        $processor = new Processor();

        return $processor->processConfiguration($configuration, $configs);
    }
}
