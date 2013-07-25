<?php

namespace EasyTask\Bundle\WorkflowBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('workflow');

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('workflows')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('id')->end()
                            ->scalarNode('class')->end()
                            ->arrayNode('routes')
                                ->children()
                                    ->scalarNode('prefix')
                                        ->defaultValue('')
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('actions')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('nodes')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->booleanNode('bootstrap')->end()
                                        ->arrayNode('route')
                                            ->isRequired()
                                            ->children()
                                                ->scalarNode('name')->end()
                                                ->scalarNode('pattern')->isRequired()->end()
                                            ->end()
                                        ->end()
                                        ->arrayNode('controller')
                                            ->isRequired()
                                            ->children()
                                                ->scalarNode('class')->end()
                                                ->scalarNode('id')->end()
                                                ->arrayNode('actions')
                                                    ->useAttributeAsKey('name')
                                                    ->prototype('scalar')->end()
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
