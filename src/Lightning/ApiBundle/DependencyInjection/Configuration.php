<?php

namespace Lightning\ApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration
 *
 * @codeCoverageIgnore
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lightning_api');

        $rootNode
            ->children()
            ->scalarNode('urbanairship_key')->defaultValue('')->end()
            ->scalarNode('urbanairship_secret')->defaultValue('')->end()
            ->scalarNode('appstore_verify_url')->defaultValue('')->end()
            ->end();

        return $treeBuilder;
    }
}
