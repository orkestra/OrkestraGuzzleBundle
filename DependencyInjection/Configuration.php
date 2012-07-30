<?php

namespace Orkestra\Bundle\GuzzleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Guzzle Configuration.
 *
 * @author Zach Badgett <zach.badgett@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    private $debug;

    /**
     * Constructor.
     *
     * @param Boolean $debug The kernel.debug value
     */
    public function __construct($debug)
    {
        $this->debug = (Boolean) $debug;
    }

    /**
     * Create configuration tree.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('guzzle');
        $rootNode
                ->children()
                    ->arrayNode('services')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('class')->end()
                                ->arrayNode('services')
                                    ->prototype('array')
                                        ->children()
                                            ->scalarNode('id')->end()
                                        ->end()
                                    ->end()
                                ->end()
                                ->arrayNode('params')
                                    ->useAttributeAsKey('key')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                    ->end()
                ->end()
            ->end();
        
        return $treeBuilder;
    }
}