<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('exbico_monolog_db');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('connection')
                    ->defaultValue('doctrine.dbal.connection')
                ->end()
                ->integerNode('history_size')
                    ->defaultValue(2)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
