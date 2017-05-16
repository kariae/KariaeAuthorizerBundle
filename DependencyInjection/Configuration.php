<?php

namespace Kariae\AuthorizerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kariae_authorizer');

        $rootNode
            ->children()
                // User provider parameters
                ->scalarNode('user_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                // Cache parameters
                ->arrayNode('cache')
                    ->canBeUnset()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultValue(false)->end()
                        ->scalarNode('adapter')
                            ->validate()
                                ->ifNotInArray(array('redis'))
                                ->thenInvalid('Invalid cache adapter %s')
                            ->end()
                        ->end()
                        ->arrayNode('redis')
                            ->children()
                                ->scalarNode('host')
                                    ->cannotBeEmpty()
                                    ->isRequired()
                                ->end()
                                ->integerNode('port')->defaultValue(6379)->end()
                            ->end()
                        ->end()
                    ->end()
                    ->validate() // Validate cache parameter
                        ->ifTrue(function ($v) {
                            if ($v['enabled']) {
                                // Adapter should be required
                                if (!isset($v['adapter']) ||
                                    !isset($v[$v['adapter']])) {
                                    return true;
                                }
                            }
                        })
                        ->thenInvalid('Please enter the configuration for the
                            selected cache adapter')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
