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
                ->scalarNode('default_theme')->defaultValue('default')->end()
                ->scalarNode('default_variant')->defaultValue('default')->end()
                ->scalarNode('schema_version')->defaultValue('1.0')->end()
                ->arrayNode('user_tokens')
                    ->normalizeKeys(false)
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode('system_profile')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('id')->defaultValue('chameleon-default')->end()
                        ->integerNode('columns')->defaultValue(12)->min(1)->max(24)->end()
                        ->arrayNode('breakpoints')
                            ->normalizeKeys(false)
                            ->integerPrototype()->min(1)->end()
                            ->defaultValue([])
                        ->end()
                        ->arrayNode('container_max_widths')
                            ->normalizeKeys(false)
                            ->integerPrototype()->min(1)->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
