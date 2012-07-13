<?php

namespace Orkestra\Bundle\GuzzleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
                                ->scalarNode('class')
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