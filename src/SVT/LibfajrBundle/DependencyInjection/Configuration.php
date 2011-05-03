<?php
/**
 * This file contains configuration definition for SVTLibfajrBundle.
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    LibfajrBundle
 * @subpackage DependencyInjection
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace SVT\LibfajrBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration definition for SVTLibfajrBundle
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder() {
        $tb = new TreeBuilder();
        $rootNode = $tb->root('libfajr');

        $rootNode
            ->children()
                ->scalarNode('server')->cannotBeEmpty()->end()
                ->scalarNode('cookie')->cannotBeEmpty()->end()
                ->scalarNode('trace')->defaultValue('null')->end()
                ->arrayNode('login')
                    ->cannotBeEmpty()
                    ->children()
                        ->scalarNode('cosign_proxy')->cannotBeEmpty()->end()
                        ->scalarNode('cosign_cookie')->end()
                    ->end()
                ->end()
                ->arrayNode('connection')
                    ->cannotBeEmpty()
                    ->children()
                        ->arrayNode('curl')
                            ->children()
                                ->booleanNode('transient')->defaultFalse()->end()
                                ->scalarNode('directory')->cannotBeEmpty()->end()
                                ->scalarNode('user_agent')->defaultValue('libfajr')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $tb;
    }
}