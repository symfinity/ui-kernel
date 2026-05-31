<?php

declare(strict_types=1);

namespace Symfinity\UiKernel\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('symfinity_ui_kernel');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('default_flavour')->defaultValue('default')->end()
                ->scalarNode('schema_version')->defaultValue('2.0')->end()
                ->arrayNode('user_tokens')
                    ->normalizeKeys(false)
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
