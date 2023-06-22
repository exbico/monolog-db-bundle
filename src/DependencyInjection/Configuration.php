<?php

declare(strict_types=1);

namespace Exbico\MonologDbBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('exbico_monolog_db');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('connection')->defaultValue('doctrine.dbal.connection')->end()
                ->arrayNode('levels')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('emergency')->defaultValue('log_emergency')->end()
                        ->scalarNode('alert')->defaultValue('log_alert')->end()
                        ->scalarNode('critical')->defaultValue('log_critical')->end()
                        ->scalarNode('error')->defaultValue('log_error')->end()
                        ->scalarNode('warning')->defaultValue('log_warning')->end()
                        ->scalarNode('notice')->defaultValue('log_notice')->end()
                        ->scalarNode('info')->defaultValue('log_info')->end()
                        ->scalarNode('debug')->defaultValue('log_debug')->end()
                    ->end()
                ->end()
                ->arrayNode('rotation')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('history_size')->defaultValue(2)->end()
                        ->scalarNode('date_format')->defaultValue('YmdHis')->end()
                    ?->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
