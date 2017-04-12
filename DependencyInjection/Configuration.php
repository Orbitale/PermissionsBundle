<?php

/**
 * This file is part of the AccessRules package.
 *
 * (c) Alexandre Rock Ancelet <alex@orbitale.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Orbitale\Bundle\PermissionsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('permissions');

        $rootNode
            ->children()
                ->arrayNode('defaults')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('expression_variables')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('supports')->defaultNull()->end()
                    ->end()
                ->end()
                ->arrayNode('rules')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($expression) {
                                return [
                                    'supports' => null,
                                    'on_vote' => $expression,
                                ];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('supports')->defaultNull()->end()
                            ->scalarNode('on_vote')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
